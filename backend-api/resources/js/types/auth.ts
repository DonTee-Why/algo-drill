export interface AuthUser {
    id: number;
    name: string;
    username?: string;
    email: string;
    preferred_languages?: string[];
}

export interface AuthProps {
    auth?: {
        user?: AuthUser | null;
    };
}
