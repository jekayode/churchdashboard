@include('components.permissions.users-page', [
    'pageTitle' => 'Manage Users',
    'apiUsersUrl' => '/api/admin/users',
    'isSuperAdmin' => $isSuperAdmin ?? true,
    'fixedBranchId' => $fixedBranchId ?? null,
])
