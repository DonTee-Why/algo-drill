import { SESSION_DIFFICULTY_BADGE_COLORS } from '../constants';

export function getDifficultyBadgeColor(difficulty: string): string {
    return SESSION_DIFFICULTY_BADGE_COLORS[difficulty] || 'gray';
}

export function getStageBadgeColor(state: string): string {
    if (state === 'DONE') {
        return 'green';
    }

    return 'blue';
}

export function formatRelativeDate(dateString: string): string {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) {
        return 'Just now';
    }

    if (diffMins < 60) {
        return `${diffMins} min${diffMins > 1 ? 's' : ''} ago`;
    }

    if (diffHours < 24) {
        return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    }

    if (diffDays < 7) {
        return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    }

    return date.toLocaleDateString();
}
