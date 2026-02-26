# Guest Follow-Up Email Campaign Setup Guide

This guide explains how to implement automated guest follow-up emails using the Email Campaign system.

## Overview

The system automatically enrolls guests in email campaigns when they register. You can create campaigns with multiple steps that send emails at specific intervals (e.g., 7 days, 14 days, 30 days after registration).

## How It Works

1. **Guest Registers** → Automatically enrolled in active campaigns with trigger `guest-registration`
2. **Scheduled Processing** → Every 5 minutes, the system checks for due emails
3. **Email Sent** → Guest receives the email based on the campaign step
4. **Next Step Scheduled** → System schedules the next email based on `delay_days`

## Step-by-Step Setup

### Step 1: Create Email Templates

Before creating campaigns, you need email templates for each follow-up stage:

1. Go to **Communication → Templates**
2. Create templates for each follow-up stage:

   **Template 1: Week 1 Welcome**
   - Name: "Guest Welcome - Week 1"
   - Subject: "Welcome to {branch_name}, {recipient_name}!"
   - Content: Include welcome message, church information, next steps

   **Template 2: Week 2 Resources**
   - Name: "Guest Follow-Up - Week 2"
   - Subject: "Resources to Help You Get Connected"
   - Content: Share small groups, events, resources

   **Template 3: Month 1 Invitation**
   - Name: "Guest Follow-Up - Month 1"
   - Subject: "Join Us This Sunday!"
   - Content: Invite to upcoming events, services

   **Template 4: Month 2 Engagement**
   - Name: "Guest Follow-Up - Month 2"
   - Subject: "Ways to Get Involved"
   - Content: Ministries, volunteer opportunities

   **Template 5: Month 3 Deep Dive**
   - Name: "Guest Follow-Up - Month 3"
   - Subject: "Taking the Next Step"
   - Content: Membership classes, deeper engagement

### Step 2: Create Email Campaign

1. Go to **Communication → Campaigns**
2. Click **"Create Campaign"**
3. Fill in the form:
   - **Name**: "Guest Follow-Up Sequence"
   - **Trigger Event**: Select **"Guest Registration"**
   - **Active**: Check this box to enable the campaign

### Step 3: Add Campaign Steps

Add steps in order with appropriate delays:

**Step 1: Week 1 Welcome**
- Step Order: 1
- Delay Days: 7 (sends 7 days after registration)
- Template: Select "Guest Welcome - Week 1"

**Step 2: Week 2 Resources**
- Step Order: 2
- Delay Days: 14 (sends 14 days after registration)
- Template: Select "Guest Follow-Up - Week 2"

**Step 3: Month 1 Invitation**
- Step Order: 3
- Delay Days: 30 (sends 30 days after registration)
- Template: Select "Guest Follow-Up - Month 1"

**Step 4: Month 2 Engagement**
- Step Order: 4
- Delay Days: 60 (sends 60 days after registration)
- Template: Select "Guest Follow-Up - Month 2"

**Step 5: Month 3 Deep Dive**
- Step Order: 5
- Delay Days: 90 (sends 90 days after registration)
- Template: Select "Guest Follow-Up - Month 3"

### Step 4: Activate Campaign

1. Make sure **"Active"** is checked
2. Click **"Save Campaign"**
3. The campaign is now live!

## How Delay Days Work

- **Delay Days** = Days after enrollment (registration) before sending
- Step 1 with delay_days = 7 → Sends 7 days after guest registers
- Step 2 with delay_days = 14 → Sends 14 days after guest registers
- Each step calculates its send date from the **original enrollment date**, not the previous step

**Important**: If you want emails spaced apart (e.g., 7 days, then 7 more days), calculate:
- Step 1: delay_days = 7
- Step 2: delay_days = 14 (7 + 7)
- Step 3: delay_days = 30 (7 + 7 + 16)

## Best Practices

### Email Content Tips

1. **Week 1 (Day 7)**:
   - Welcome message
   - Thank them for visiting
   - Share church values and mission
   - Include contact information

2. **Week 2 (Day 14)**:
   - Share resources (sermons, small groups)
   - Invite to upcoming events
   - Provide ways to connect

3. **Month 1 (Day 30)**:
   - Personal invitation to return
   - Highlight specific events
   - Share testimonies

4. **Month 2 (Day 60)**:
   - Deeper engagement opportunities
   - Ministry involvement
   - Volunteer opportunities

5. **Month 3 (Day 90)**:
   - Membership information
   - Next steps for commitment
   - Personal connection offer

### Template Variables

Use these variables in your templates:
- `{recipient_name}` - Guest's name
- `{branch_name}` - Church branch name
- `{branch_email}` - Branch contact email
- `{branch_phone}` - Branch phone number
- `{branch_venue}` - Service location
- `{app_name}` - Application name
- `{current_date}` - Current date
- `{current_year}` - Current year

### Frequency Guidelines

- **Don't send too frequently**: Space emails at least 7 days apart
- **Respect preferences**: Include unsubscribe option
- **Personalize**: Use recipient's name and relevant information
- **Track engagement**: Monitor open rates and adjust content

## Monitoring Campaigns

### View Campaign Statistics

1. Go to **Communication → Campaigns**
2. Click on a campaign to view:
   - Total enrollments
   - Active enrollments
   - Completed enrollments
   - Completion rate

### View Individual Enrollments

- Check Communication Logs to see which emails were sent
- Filter by campaign or guest to track progress

### Testing

Before activating:
1. Create the campaign with `is_active = false`
2. Manually enroll a test user
3. Use `php artisan campaigns:process` to test
4. Verify emails are sent correctly
5. Activate the campaign

## Troubleshooting

### Guests Not Receiving Emails

1. **Check Campaign Status**: Ensure campaign is active
2. **Check Queue**: Run `php artisan queue:work` if using async processing
3. **Check Scheduler**: Ensure `php artisan schedule:run` is running
4. **Check Logs**: Review `storage/logs/laravel.log` for errors
5. **Check Email Settings**: Verify email provider is configured correctly

### Emails Sent Too Early/Late

- Review `delay_days` settings in campaign steps
- Remember: delay_days is from enrollment date, not previous step

### Duplicate Emails

- The system prevents duplicate enrollments
- Each guest can only be enrolled once per campaign
- Check if guest was enrolled multiple times manually

## Advanced: Custom Campaigns

You can create multiple campaigns for different purposes:

- **New Guest Welcome** (Week 1 only)
- **Guest Re-engagement** (For guests who haven't visited in a while)
- **Event-Specific Follow-Up** (After special events)

Each campaign can have different triggers and step sequences.

## Command Reference

```bash
# Process campaigns manually (synchronous)
php artisan campaigns:process

# Process campaigns asynchronously (recommended)
php artisan campaigns:process-async

# Dry run (see what would be sent without sending)
php artisan campaigns:process --dry-run
```

## Scheduled Processing

The system automatically processes campaigns every 5 minutes via Laravel's scheduler. Ensure your cron is set up:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Example Campaign Configuration

**Campaign Name**: "Guest Follow-Up Sequence"
**Trigger**: guest-registration
**Active**: Yes

**Steps**:
1. Step 1: delay_days = 7, Template = "Week 1 Welcome"
2. Step 2: delay_days = 14, Template = "Week 2 Resources"  
3. Step 3: delay_days = 30, Template = "Month 1 Invitation"
4. Step 4: delay_days = 60, Template = "Month 2 Engagement"
5. Step 5: delay_days = 90, Template = "Month 3 Deep Dive"

This will send emails at:
- Day 7: Welcome email
- Day 14: Resources email
- Day 30: Month 1 invitation
- Day 60: Month 2 engagement
- Day 90: Month 3 deep dive

## Support

For issues or questions:
1. Check the Communication Logs for email delivery status
2. Review Laravel logs for errors
3. Verify email provider settings in Communication Settings
4. Test with a dry run before activating campaigns

