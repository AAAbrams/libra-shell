export const appName = import.meta.env.VITE_APP_NAME || 'Libra Shell'

export const pageLookupPaths = (
    import.meta.env.VITE_SHELL_PAGE_PATHS || 'resources/js/pages'
)
    .split(/[\n,]/)
    .map((path: string) => path.trim())
    .filter(Boolean)

export const pages = import.meta.glob<{ default: React.ComponentType }>([
    '@shell-pages/**/*.tsx',
    '@shell-extra-pages/**/*.tsx',
])
