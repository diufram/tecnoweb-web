import { onMounted, ref } from 'vue';

export type ColorMode = 'light' | 'dark' | 'system' | 'schedule';
export type AudienceTheme = 'kids' | 'youth' | 'adults';
export type TextScale = 'normal' | 'large' | 'extra-large';
export type Contrast = 'normal' | 'high';

export interface AppearancePreferences {
    colorMode: ColorMode;
    audienceTheme: AudienceTheme;
    textScale: TextScale;
    contrast: Contrast;
}

const STORAGE_KEY = 'appearance-preferences';
const LEGACY_STORAGE_KEY = 'appearance';

const defaultPreferences: AppearancePreferences = {
    colorMode: 'system',
    audienceTheme: 'adults',
    textScale: 'normal',
    contrast: 'normal',
};

const colorModeClasses: ColorMode[] = ['light', 'dark', 'system', 'schedule'];
const themeClasses: AudienceTheme[] = ['kids', 'youth', 'adults'];
const textScaleClasses: TextScale[] = ['normal', 'large', 'extra-large'];
const contrastClasses: Contrast[] = ['normal', 'high'];

const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

const getScheduledTheme = () => {
    const hour = new Date().getHours();

    return hour >= 19 || hour < 6 ? 'dark' : 'light';
};

const getEffectiveTheme = (colorMode: ColorMode) => {
    if (colorMode === 'system') {
        return mediaQuery.matches ? 'dark' : 'light';
    }

    if (colorMode === 'schedule') {
        return getScheduledTheme();
    }

    return colorMode;
};

const isColorMode = (value: string | null): value is ColorMode => colorModeClasses.includes(value as ColorMode);

const readPreferences = (): AppearancePreferences => {
    const saved = localStorage.getItem(STORAGE_KEY);

    if (saved) {
        try {
            return { ...defaultPreferences, ...JSON.parse(saved) };
        } catch {
            localStorage.removeItem(STORAGE_KEY);
        }
    }

    const legacyAppearance = localStorage.getItem(LEGACY_STORAGE_KEY);

    if (isColorMode(legacyAppearance)) {
        return {
            ...defaultPreferences,
            colorMode: legacyAppearance,
        };
    }

    return defaultPreferences;
};

export function applyAppearance(preferences: AppearancePreferences) {
    const root = document.documentElement;
    const effectiveTheme = getEffectiveTheme(preferences.colorMode);

    root.classList.toggle('dark', effectiveTheme === 'dark');
    root.dataset.colorMode = preferences.colorMode;

    themeClasses.forEach((theme) => root.classList.remove(`theme-${theme}`));
    textScaleClasses.forEach((scale) => root.classList.remove(`text-scale-${scale}`));
    contrastClasses.forEach((contrast) => root.classList.remove(`contrast-${contrast}`));

    root.classList.add(`theme-${preferences.audienceTheme}`);
    root.classList.add(`text-scale-${preferences.textScale}`);
    root.classList.add(`contrast-${preferences.contrast}`);
}

const handleSystemThemeChange = () => {
    const preferences = readPreferences();
    applyAppearance(preferences);
};

export function initializeTheme() {
    const preferences = readPreferences();
    localStorage.setItem(STORAGE_KEY, JSON.stringify(preferences));
    applyAppearance(preferences);
    mediaQuery.addEventListener('change', handleSystemThemeChange);
}

export function useAppearance() {
    const preferences = ref<AppearancePreferences>({ ...defaultPreferences });

    onMounted(() => {
        initializeTheme();
        preferences.value = readPreferences();
    });

    function updatePreferences(value: Partial<AppearancePreferences>) {
        preferences.value = {
            ...preferences.value,
            ...value,
        };

        localStorage.setItem(STORAGE_KEY, JSON.stringify(preferences.value));
        localStorage.setItem(LEGACY_STORAGE_KEY, preferences.value.colorMode);
        applyAppearance(preferences.value);
    }

    return {
        preferences,
        updatePreferences,
    };
}
