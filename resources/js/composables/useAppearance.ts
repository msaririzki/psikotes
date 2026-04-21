import type { ComputedRef, Ref } from 'vue';
import { computed, ref } from 'vue';
import type { Appearance, ResolvedAppearance } from '@/types';

export type { Appearance, ResolvedAppearance };

export type UseAppearanceReturn = {
    appearance: Ref<Appearance>;
    resolvedAppearance: ComputedRef<ResolvedAppearance>;
    updateAppearance: (value: Appearance) => void;
};

let isThemeInitialized = false;

const mediaQuery = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

const getStoredAppearance = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem('appearance') as Appearance | null;
};

const getSystemAppearance = (): ResolvedAppearance =>
    mediaQuery()?.matches ? 'dark' : 'light';

const resolveAppearance = (value: Appearance): ResolvedAppearance =>
    value === 'system' ? getSystemAppearance() : value;

const appearance = ref<Appearance>(getStoredAppearance() || 'system');
const systemAppearance = ref<ResolvedAppearance>(getSystemAppearance());

export function updateTheme(value: Appearance): void {
    if (typeof window === 'undefined') {
        return;
    }

    const activeAppearance = resolveAppearance(value);

    document.documentElement.classList.toggle(
        'dark',
        activeAppearance === 'dark',
    );
    document.documentElement.style.colorScheme = activeAppearance;
}

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;

    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const handleSystemThemeChange = () => {
    const currentAppearance = getStoredAppearance();
    systemAppearance.value = getSystemAppearance();

    updateTheme(currentAppearance || 'system');
};

export function initializeTheme(): void {
    if (typeof window === 'undefined' || isThemeInitialized) {
        return;
    }

    const savedAppearance = getStoredAppearance();
    appearance.value = savedAppearance || 'system';
    systemAppearance.value = getSystemAppearance();
    updateTheme(savedAppearance || 'system');

    mediaQuery()?.addEventListener('change', handleSystemThemeChange);
    isThemeInitialized = true;
}

export function useAppearance(): UseAppearanceReturn {
    const resolvedAppearance = computed<ResolvedAppearance>(() => {
        if (appearance.value === 'system') {
            return systemAppearance.value;
        }

        return appearance.value;
    });

    function updateAppearance(value: Appearance) {
        appearance.value = value;

        // Store in localStorage for client-side persistence...
        localStorage.setItem('appearance', value);

        // Store in cookie for SSR...
        setCookie('appearance', value);

        updateTheme(value);
    }

    return {
        appearance,
        resolvedAppearance,
        updateAppearance,
    };
}
