export type User = {
    id: number;
    name: string;
    email: string;
    role: 'user' | 'admin' | 'super_admin';
    avatar?: string;
    onboarding_completed: boolean;
    is_active: boolean;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
    can: {
        accessAdminArea: boolean;
    };
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
