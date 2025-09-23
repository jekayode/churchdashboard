<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CommunicationSetting;
use App\Services\CommunicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

final class CommunicationSettingController extends Controller
{
    public function __construct(
        private readonly CommunicationService $communicationService
    ) {}

    /**
     * Display communication settings for a branch.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $branchId = $request->integer('branch_id');

        // Authorization
        if (! $user->isSuperAdmin() && (! $branchId || $user->getActiveBranchId() !== $branchId)) {
            $branchId = $user->getActiveBranchId();
        }

        if (! $branchId) {
            return response()->json(['error' => 'Branch not specified'], 400);
        }

        $branch = Branch::findOrFail($branchId);
        Gate::authorize('manageSettings', $branch);

        $setting = $branch->communicationSetting;

        return response()->json([
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
            ],
            'setting' => $setting ? [
                'id' => $setting->id,
                'email_provider' => $setting->email_provider,
                'email_config' => $setting->email_config ?? [],
                'sms_provider' => $setting->sms_provider,
                'sms_config' => $setting->sms_config ?? [],
                'whatsapp_provider' => $setting->whatsapp_provider,
                'whatsapp_config' => $setting->whatsapp_config ?? [],
                'birthday_template_id' => $setting->birthday_template_id,
                'anniversary_template_id' => $setting->anniversary_template_id,
                'auto_send_birthdays' => $setting->auto_send_birthdays,
                'auto_send_anniversaries' => $setting->auto_send_anniversaries,
                'from_name' => $setting->from_name,
                'from_email' => $setting->from_email,
                'is_active' => $setting->is_active,
                'has_email_config' => ! empty($setting->email_config),
                'has_sms_config' => ! empty($setting->sms_config),
                'has_whatsapp_config' => ! empty($setting->whatsapp_config),
                'created_at' => $setting->created_at,
                'updated_at' => $setting->updated_at,
            ] : null,
            'available_providers' => [
                'email' => ['smtp', 'resend', 'mailgun', 'ses', 'postmark'],
                'sms' => ['twilio', 'africas-talking', 'jusibe', 'bulksmsnigeria'],
                'whatsapp' => ['twilio', 'meta'],
            ],
        ]);
    }

    /**
     * Store or update communication settings for a branch.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $branchId = $request->integer('branch_id');

        // Authorization
        if (! $user->isSuperAdmin() && (! $branchId || $user->getActiveBranchId() !== $branchId)) {
            $branchId = $user->getActiveBranchId();
        }

        if (! $branchId) {
            return response()->json(['error' => 'Branch not specified'], 400);
        }

        $branch = Branch::findOrFail($branchId);
        Gate::authorize('manageSettings', $branch);

        // Debug logging
        Log::info('Communication settings save request', [
            'user_id' => $user->id,
            'branch_id' => $branchId,
            'request_data' => $request->all(),
        ]);

        // Validation
        $validator = Validator::make($request->all(), [
            'email_provider' => 'required|in:smtp,resend,mailgun,ses,postmark',
            'email_config' => 'required|array',
            'sms_provider' => 'nullable|in:twilio,africas-talking,jusibe,bulksmsnigeria',
            'sms_config' => 'nullable|array',
            'whatsapp_provider' => 'nullable|in:twilio,meta',
            'whatsapp_config' => 'nullable|array',
            'birthday_template_id' => 'nullable|exists:message_templates,id',
            'anniversary_template_id' => 'nullable|exists:message_templates,id',
            'auto_send_birthdays' => 'boolean',
            'auto_send_anniversaries' => 'boolean',
            'from_name' => 'nullable|string|max:255',
            'from_email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validate provider-specific config
        $emailConfigValidation = $this->validateEmailConfig(
            $request->input('email_provider'),
            $request->input('email_config', [])
        );

        if (! $emailConfigValidation['valid']) {
            return response()->json(['errors' => ['email_config' => $emailConfigValidation['errors']]], 422);
        }

        if ($request->filled('sms_provider') && $request->filled('sms_config')) {
            $smsConfigValidation = $this->validateSmsConfig(
                $request->input('sms_provider'),
                $request->input('sms_config', [])
            );

            if (! $smsConfigValidation['valid']) {
                return response()->json(['errors' => ['sms_config' => $smsConfigValidation['errors']]], 422);
            }
        }

        if ($request->filled('whatsapp_provider') && $request->filled('whatsapp_config')) {
            $whatsappConfigValidation = $this->validateWhatsAppConfig(
                $request->input('whatsapp_provider'),
                $request->input('whatsapp_config', [])
            );

            if (! $whatsappConfigValidation['valid']) {
                return response()->json(['errors' => ['whatsapp_config' => $whatsappConfigValidation['errors']]], 422);
            }
        }

        try {
            // Create or update settings
            $setting = CommunicationSetting::updateOrCreate(
                ['branch_id' => $branch->id],
                [
                    'email_provider' => $request->input('email_provider'),
                    'email_config' => $request->input('email_config'),
                    'sms_provider' => $request->input('sms_provider'),
                    'sms_config' => $request->input('sms_config'),
                    'whatsapp_provider' => $request->input('whatsapp_provider'),
                    'whatsapp_config' => $request->input('whatsapp_config'),
                    'birthday_template_id' => $request->input('birthday_template_id'),
                    'anniversary_template_id' => $request->input('anniversary_template_id'),
                    'auto_send_birthdays' => $request->boolean('auto_send_birthdays', false),
                    'auto_send_anniversaries' => $request->boolean('auto_send_anniversaries', false),
                    'from_name' => $request->input('from_name'),
                    'from_email' => $request->input('from_email'),
                    'is_active' => $request->boolean('is_active', true),
                ]
            );

            Log::info('Communication settings updated', [
                'branch_id' => $branch->id,
                'user_id' => $user->id,
                'email_provider' => $setting->email_provider,
                'sms_provider' => $setting->sms_provider,
            ]);

            return response()->json([
                'message' => 'Communication settings saved successfully',
                'setting' => [
                    'id' => $setting->id,
                    'email_provider' => $setting->email_provider,
                    'sms_provider' => $setting->sms_provider,
                    'from_name' => $setting->from_name,
                    'from_email' => $setting->from_email,
                    'is_active' => $setting->is_active,
                    'has_email_config' => ! empty($setting->email_config),
                    'has_sms_config' => ! empty($setting->sms_config),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to save communication settings', [
                'branch_id' => $branch->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to save settings'], 500);
        }
    }

    /**
     * Test communication settings.
     */
    public function test(Request $request): JsonResponse
    {
        $user = $request->user();
        $branchId = $request->integer('branch_id');

        // Authorization
        if (! $user->isSuperAdmin() && (! $branchId || $user->getActiveBranchId() !== $branchId)) {
            $branchId = $user->getActiveBranchId();
        }

        if (! $branchId) {
            return response()->json(['error' => 'Branch not specified'], 400);
        }

        $branch = Branch::findOrFail($branchId);
        Gate::authorize('manageSettings', $branch);

        $setting = $branch->communicationSetting;

        if (! $setting || ! $setting->is_active) {
            return response()->json(['error' => 'Communication not configured for this branch'], 400);
        }

        try {
            $results = $this->communicationService->testCommunicationSettings($setting);

            Log::info('Communication settings tested', [
                'branch_id' => $branch->id,
                'user_id' => $user->id,
                'results' => $results,
            ]);

            return response()->json([
                'message' => 'Communication test completed',
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Communication test failed', [
                'branch_id' => $branch->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Test failed: '.$e->getMessage()], 500);
        }
    }

    /**
     * Get configuration template for a provider.
     */
    public function getProviderTemplate(Request $request): JsonResponse
    {
        $provider = $request->input('provider');
        $type = $request->input('type'); // 'email', 'sms', or 'whatsapp'

        if ($type === 'email') {
            $template = $this->getEmailProviderTemplate($provider);
        } elseif ($type === 'sms') {
            $template = $this->getSmsProviderTemplate($provider);
        } elseif ($type === 'whatsapp') {
            $template = $this->getWhatsAppProviderTemplate($provider);
        } else {
            return response()->json(['error' => 'Invalid type'], 400);
        }

        if (! $template) {
            return response()->json(['error' => 'Provider not supported'], 400);
        }

        return response()->json(['template' => $template]);
    }

    /**
     * Validate email provider configuration.
     */
    private function validateEmailConfig(string $provider, array $config): array
    {
        $errors = [];

        switch ($provider) {
            case 'smtp':
                if (empty($config['host'])) {
                    $errors[] = 'SMTP host is required';
                }
                if (empty($config['port'])) {
                    $errors[] = 'SMTP port is required';
                }
                if (empty($config['username'])) {
                    $errors[] = 'SMTP username is required';
                }
                if (empty($config['password'])) {
                    $errors[] = 'SMTP password is required';
                }
                break;

            case 'resend':
                if (empty($config['api_key'])) {
                    $errors[] = 'Resend API key is required';
                }
                break;

            case 'mailgun':
                if (empty($config['api_key'])) {
                    $errors[] = 'Mailgun API key is required';
                }
                if (empty($config['domain'])) {
                    $errors[] = 'Mailgun domain is required';
                }
                break;

            case 'postmark':
                if (empty($config['api_key'])) {
                    $errors[] = 'Postmark API key is required';
                }
                break;

            case 'ses':
                if (empty($config['access_key'])) {
                    $errors[] = 'AWS access key is required';
                }
                if (empty($config['secret_key'])) {
                    $errors[] = 'AWS secret key is required';
                }
                if (empty($config['region'])) {
                    $errors[] = 'AWS region is required';
                }
                break;
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Validate SMS provider configuration.
     */
    private function validateSmsConfig(string $provider, array $config): array
    {
        $errors = [];

        switch ($provider) {
            case 'twilio':
                if (empty($config['account_sid'])) {
                    $errors[] = 'Twilio Account SID is required';
                }
                if (empty($config['auth_token'])) {
                    $errors[] = 'Twilio Auth Token is required';
                }
                if (empty($config['from_number'])) {
                    $errors[] = 'Twilio From Number is required';
                }
                break;

            case 'africas-talking':
                if (empty($config['api_key'])) {
                    $errors[] = 'Africa\'s Talking API key is required';
                }
                if (empty($config['username'])) {
                    $errors[] = 'Africa\'s Talking username is required';
                }
                break;

            case 'jusibe':
                if (empty($config['public_key'])) {
                    $errors[] = 'Jusibe Public Key is required';
                }
                if (empty($config['access_token'])) {
                    $errors[] = 'Jusibe Access Token is required';
                }
                if (empty($config['sender_id'])) {
                    $errors[] = 'Jusibe Sender ID is required';
                }
                break;

            case 'bulksmsnigeria':
                if (empty($config['username'])) {
                    $errors[] = 'Bulksmsnigeria Username is required';
                }
                if (empty($config['password'])) {
                    $errors[] = 'Bulksmsnigeria Password is required';
                }
                if (empty($config['sender_id'])) {
                    $errors[] = 'Bulksmsnigeria Sender ID is required';
                }
                break;
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Validate WhatsApp provider configuration.
     */
    private function validateWhatsAppConfig(string $provider, array $config): array
    {
        $errors = [];

        switch ($provider) {
            case 'twilio':
                if (empty($config['account_sid'])) {
                    $errors[] = 'Twilio Account SID is required';
                }
                if (empty($config['auth_token'])) {
                    $errors[] = 'Twilio Auth Token is required';
                }
                if (empty($config['from_number'])) {
                    $errors[] = 'Twilio WhatsApp From Number is required';
                }
                break;

            case 'meta':
                if (empty($config['access_token'])) {
                    $errors[] = 'Meta Access Token is required';
                }
                if (empty($config['phone_number_id'])) {
                    $errors[] = 'Meta Phone Number ID is required';
                }
                break;
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Get email provider configuration template.
     */
    private function getEmailProviderTemplate(string $provider): ?array
    {
        return match ($provider) {
            'smtp' => [
                'host' => ['type' => 'text', 'label' => 'SMTP Host', 'placeholder' => 'smtp.gmail.com'],
                'port' => ['type' => 'number', 'label' => 'SMTP Port', 'placeholder' => '587'],
                'encryption' => ['type' => 'select', 'label' => 'Encryption', 'options' => ['tls', 'ssl', 'none']],
                'username' => ['type' => 'text', 'label' => 'Username', 'placeholder' => 'your-email@domain.com'],
                'password' => ['type' => 'password', 'label' => 'Password', 'placeholder' => 'Your email password'],
            ],
            'resend' => [
                'api_key' => ['type' => 'password', 'label' => 'Resend API Key', 'placeholder' => 'Your Resend API key'],
            ],
            'mailgun' => [
                'api_key' => ['type' => 'password', 'label' => 'Mailgun API Key', 'placeholder' => 'Your Mailgun API key'],
                'domain' => ['type' => 'text', 'label' => 'Domain', 'placeholder' => 'yourdomain.com'],
            ],
            'postmark' => [
                'api_key' => ['type' => 'password', 'label' => 'Postmark Server Token', 'placeholder' => 'Your Postmark server token'],
            ],
            'ses' => [
                'access_key' => ['type' => 'text', 'label' => 'AWS Access Key', 'placeholder' => 'Your AWS access key'],
                'secret_key' => ['type' => 'password', 'label' => 'AWS Secret Key', 'placeholder' => 'Your AWS secret key'],
                'region' => ['type' => 'text', 'label' => 'AWS Region', 'placeholder' => 'us-east-1'],
            ],
            default => null,
        };
    }

    /**
     * Get SMS provider configuration template.
     */
    private function getSmsProviderTemplate(string $provider): ?array
    {
        return match ($provider) {
            'twilio' => [
                'account_sid' => ['type' => 'text', 'label' => 'Account SID', 'placeholder' => 'Your Twilio Account SID'],
                'auth_token' => ['type' => 'password', 'label' => 'Auth Token', 'placeholder' => 'Your Twilio Auth Token'],
                'from_number' => ['type' => 'text', 'label' => 'From Number', 'placeholder' => '+1234567890'],
            ],
            'africas-talking' => [
                'api_key' => ['type' => 'password', 'label' => 'API Key', 'placeholder' => 'Your Africa\'s Talking API key'],
                'username' => ['type' => 'text', 'label' => 'Username', 'placeholder' => 'Your Africa\'s Talking username'],
            ],
            'jusibe' => [
                'public_key' => ['type' => 'text', 'label' => 'Public Key', 'placeholder' => 'Your Jusibe Public Key'],
                'access_token' => ['type' => 'password', 'label' => 'Access Token', 'placeholder' => 'Your Jusibe Access Token'],
                'sender_id' => ['type' => 'text', 'label' => 'Sender ID', 'placeholder' => 'Your Sender ID (max 11 chars)'],
            ],
            'bulksmsnigeria' => [
                'username' => ['type' => 'text', 'label' => 'Username', 'placeholder' => 'Your Bulksmsnigeria Username'],
                'password' => ['type' => 'password', 'label' => 'Password', 'placeholder' => 'Your Bulksmsnigeria Password'],
                'sender_id' => ['type' => 'text', 'label' => 'Sender ID', 'placeholder' => 'Your Sender ID'],
            ],
            default => null,
        };
    }

    /**
     * Get WhatsApp provider configuration template.
     */
    private function getWhatsAppProviderTemplate(string $provider): ?array
    {
        return match ($provider) {
            'twilio' => [
                'account_sid' => ['type' => 'text', 'label' => 'Account SID', 'placeholder' => 'Your Twilio Account SID'],
                'auth_token' => ['type' => 'password', 'label' => 'Auth Token', 'placeholder' => 'Your Twilio Auth Token'],
                'from_number' => ['type' => 'text', 'label' => 'WhatsApp From Number', 'placeholder' => '+1234567890'],
            ],
            'meta' => [
                'access_token' => ['type' => 'password', 'label' => 'Access Token', 'placeholder' => 'Your Meta Access Token'],
                'phone_number_id' => ['type' => 'text', 'label' => 'Phone Number ID', 'placeholder' => 'Your WhatsApp Phone Number ID'],
                'business_account_id' => ['type' => 'text', 'label' => 'Business Account ID', 'placeholder' => 'Your Meta Business Account ID (optional)'],
            ],
            default => null,
        };
    }
}
