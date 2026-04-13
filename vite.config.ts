import fs from 'fs'
import { defineConfig, loadEnv } from 'vite'
import react from '@vitejs/plugin-react'
import svgr from 'vite-plugin-svgr'
import path from 'path'

type RollupInput = string | string[] | Record<string, string>
const moduleRoot = __dirname
const defaultProjectRoot = moduleRoot.includes('/local/modules/')
    ? path.resolve(moduleRoot, '../../..')
    : moduleRoot
const defaultPagesRoot = path.resolve(moduleRoot, 'resources/js/pages')

function resolveBareImportsFromModuleRoot() {
    const moduleImporter = path.resolve(moduleRoot, 'resources/js/app/entries/client.tsx')

    return {
        name: 'libra-shell-external-page-imports',
        async resolveId(this: {
            resolve: (
                source: string,
                importer?: string,
                options?: { skipSelf?: boolean }
            ) => Promise<{ id: string } | null>
        }, source: string, importer?: string) {
            const isBareImport =
                !source.startsWith('.') &&
                !source.startsWith('/') &&
                !source.startsWith('@shell-pages') &&
                !source.startsWith('@shell-extra-pages') &&
                source !== '@'

            if (!importer || importer.startsWith(moduleRoot) || !isBareImport) {
                return null
            }

            return this.resolve(source, moduleImporter, { skipSelf: true })
        },
    }
}

function resolveFromRoot(value: string): string {
    return path.resolve(moduleRoot, value)
}

function resolveCssEntry(
    cssEntry: string | undefined,
    projectRoot: string
): string {
    const normalizedCssEntry = cssEntry?.trim()

    if (!normalizedCssEntry) {
        return resolveFromRoot('resources/css/app.css')
    }

    if (path.isAbsolute(normalizedCssEntry)) {
        return normalizedCssEntry
    }

    if (
        normalizedCssEntry === '.' ||
        normalizedCssEntry.startsWith('./') ||
        normalizedCssEntry.startsWith('../')
    ) {
        return path.resolve(moduleRoot, normalizedCssEntry)
    }

    return path.resolve(projectRoot, normalizedCssEntry)
}

function resolveDirectory(
    directory: string,
    projectRoot: string
): string {
    const normalizedDirectory = directory.trim()

    if (!normalizedDirectory) {
        return defaultPagesRoot
    }

    if (path.isAbsolute(normalizedDirectory)) {
        return normalizedDirectory
    }

    if (
        normalizedDirectory === '.' ||
        normalizedDirectory.startsWith('./') ||
        normalizedDirectory.startsWith('../')
    ) {
        return path.resolve(moduleRoot, normalizedDirectory)
    }

    return path.resolve(projectRoot, normalizedDirectory)
}

function resolveOutputDir(
    outDir: string,
    projectRoot: string
): string {
    const normalizedOutDir = outDir.trim()

    if (!normalizedOutDir) {
        return path.resolve(moduleRoot, 'public/build')
    }

    if (path.isAbsolute(normalizedOutDir)) {
        return normalizedOutDir
    }

    if (
        normalizedOutDir === '.' ||
        normalizedOutDir.startsWith('./') ||
        normalizedOutDir.startsWith('../') ||
        normalizedOutDir.startsWith('public/') ||
        normalizedOutDir.startsWith('bootstrap/')
    ) {
        return path.resolve(moduleRoot, normalizedOutDir)
    }

    return path.resolve(projectRoot, normalizedOutDir)
}

function resolvePublicBasePath(
    publicOutDir: string,
    explicitBase?: string
): string {
    const normalizedBase = explicitBase?.trim()

    if (normalizedBase) {
        return normalizedBase.endsWith('/') ? normalizedBase : `${normalizedBase}/`
    }

    const normalizedOutDir = publicOutDir.trim().replace(/^\/+|\/+$/g, '')

    if (!normalizedOutDir) {
        return '/'
    }

    if (normalizedOutDir.startsWith('public/')) {
        return `/${normalizedOutDir.slice('public/'.length)}/`
    }

    return `/${normalizedOutDir}/`
}

function resolveOptionalShellOverride(
    extraShellJsRoot: string,
    relativePath: string,
    fallbackPath: string
): string {
    const candidate = path.resolve(extraShellJsRoot, relativePath)

    return fs.existsSync(candidate) ? candidate : fallbackPath
}

function parseInput(rawInput: string | undefined): RollupInput {
    const fallbackInput = 'resources/js/app/entries/client.tsx'
    const normalizedInput = rawInput?.trim() || fallbackInput

    if (normalizedInput.startsWith('{')) {
        const parsedInput = JSON.parse(normalizedInput) as Record<string, string>

        return Object.fromEntries(
            Object.entries(parsedInput).map(([key, file]) => [key, resolveFromRoot(file)])
        )
    }

    const entries = normalizedInput
        .split(/[\n,]/)
        .map((entry) => entry.trim())
        .filter(Boolean)

    if (entries.length <= 1) {
        return resolveFromRoot(entries[0] || fallbackInput)
    }

    return entries.map(resolveFromRoot)
}

function resolveManualChunk(id: string): string | undefined {
    const normalizedId = id.replace(/\\/g, '/')

    if (!normalizedId.includes('/node_modules/')) {
        return undefined
    }

    if (normalizedId.includes('/node_modules/@inertiajs/')) {
        return 'vendor-inertia'
    }

    if (normalizedId.includes('/node_modules/react-dom/')) {
        return 'vendor-react-dom'
    }

    if (
        normalizedId.includes('/node_modules/react/') ||
        normalizedId.includes('/node_modules/scheduler/')
    ) {
        return 'vendor-react'
    }

    if (normalizedId.includes('/node_modules/lucide-react/')) {
        return 'vendor-icons'
    }

    if (normalizedId.includes('/node_modules/@mantine/')) {
        return 'vendor-ui'
    }

    return 'vendor'
}

export default defineConfig(({ mode, isSsrBuild }) => {
    const env = loadEnv(mode, moduleRoot, '')
    const projectRoot = env.VITE_APP_ROOT?.trim()
        ? resolveOutputDir(env.VITE_APP_ROOT, defaultProjectRoot)
        : defaultProjectRoot
    const clientOutDir = env.VITE_OUT_DIR?.trim() || 'local/assets/libra.shell/build'
    const ssrOutDir = env.VITE_SSR_OUT_DIR?.trim() || 'local/assets/libra.shell/ssr'
    const outDir = resolveOutputDir(
        isSsrBuild ? ssrOutDir : clientOutDir,
        projectRoot
    )
    const base = resolvePublicBasePath(clientOutDir, env.VITE_PUBLIC_BASE)
    const input = parseInput(env.VITE_INPUT || env.VITE_ENTRY)
    const cssEntry = resolveCssEntry(env.VITE_CSS_ENTRY, projectRoot)
    const srcDir = env.VITE_SRC_DIR?.trim() || 'resources'
    const devServerPort = Number(env.VITE_DEV_SERVER_PORT || 5173)
    const extraPagesDir = env.VITE_SHELL_EXTRA_PAGE_DIR?.trim()
        ? resolveDirectory(env.VITE_SHELL_EXTRA_PAGE_DIR, projectRoot)
        : defaultPagesRoot
    const extraShellJsRoot = path.dirname(extraPagesDir)
    const shellAppConfigRoot = resolveOptionalShellOverride(
        extraShellJsRoot,
        'app/config',
        resolveFromRoot('resources/js/app/config')
    )
    const shellAppProvidersRoot = resolveOptionalShellOverride(
        extraShellJsRoot,
        'app/providers',
        resolveFromRoot('resources/js/app/providers')
    )
    const shellIconsRoot = resolveOptionalShellOverride(
        extraShellJsRoot,
        'icons',
        path.resolve(extraShellJsRoot, 'icons')
    )

    return {
        envDir: moduleRoot,
        root: moduleRoot,
        base,
        plugins: [resolveBareImportsFromModuleRoot(), svgr(), react()],
        server: {
            host: '0.0.0.0',
            port: devServerPort,
            strictPort: true,
        },
        preview: {
            host: '0.0.0.0',
            port: devServerPort,
            strictPort: true,
        },

        build: {
            copyPublicDir: false,
            emptyOutDir: true,
            manifest: isSsrBuild ? false : 'manifest.json',
            ssrManifest: isSsrBuild ? 'ssr-manifest.json' : false,
            outDir,

            rollupOptions: isSsrBuild
                ? {
                    output: {
                        entryFileNames: 'ssr.js',
                    },
                }
                : {
                    input,
                    output: {
                        manualChunks: resolveManualChunk,
                    },
                },
        },

        ssr: {
            noExternal: true
        },

        resolve: {
            alias: [
                {
                    find: /^@libra-shell\/ui$/,
                    replacement: resolveFromRoot('resources/js/components/ui/index.ts'),
                },
                {
                    find: /^@libra-shell\/ui\/(.*)$/,
                    replacement: `${resolveFromRoot('resources/js/components/ui')}/$1`,
                },
                {
                    find: /^@libra-shell\/shared$/,
                    replacement: resolveFromRoot('resources/js/components/shared/index.ts'),
                },
                {
                    find: /^@libra-shell\/shared\/(.*)$/,
                    replacement: `${resolveFromRoot('resources/js/components/shared')}/$1`,
                },
                {
                    find: /^@libra-shell\/hooks$/,
                    replacement: resolveFromRoot('resources/js/hooks/index.ts'),
                },
                {
                    find: /^@libra-shell\/hooks\/(.*)$/,
                    replacement: `${resolveFromRoot('resources/js/hooks')}/$1`,
                },
                {
                    find: /^@libra-shell\/lib$/,
                    replacement: resolveFromRoot('resources/js/lib/index.ts'),
                },
                {
                    find: /^@libra-shell\/lib\/(.*)$/,
                    replacement: `${resolveFromRoot('resources/js/lib')}/$1`,
                },
                {
                    find: /^@libra-shell\/layouts$/,
                    replacement: resolveFromRoot('resources/js/layouts/index.ts'),
                },
                {
                    find: /^@libra-shell\/layouts\/(.*)$/,
                    replacement: `${resolveFromRoot('resources/js/layouts')}/$1`,
                },
                {
                    find: /^@libra-shell\/types$/,
                    replacement: resolveFromRoot('resources/js/types/index.ts'),
                },
                {
                    find: /^@libra-shell\/types\/(.*)$/,
                    replacement: `${resolveFromRoot('resources/js/types')}/$1`,
                },
                {
                    find: /^@libra-shell$/,
                    replacement: resolveFromRoot('resources/js/index.ts'),
                },
                {
                    find: /^@libra-shell\/(.*)$/,
                    replacement: `${resolveFromRoot('resources/js')}/$1`,
                },
                {
                    find: /^@\//,
                    replacement: `${resolveFromRoot(srcDir)}/`,
                },
                {
                    find: '@shell-pages',
                    replacement: defaultPagesRoot,
                },
                {
                    find: '@shell-extra-pages',
                    replacement: extraPagesDir,
                },
                {
                    find: '@shell-css-entry',
                    replacement: cssEntry,
                },
                {
                    find: /^@shell-app-config$/,
                    replacement: path.resolve(shellAppConfigRoot, 'index.ts'),
                },
                {
                    find: /^@shell-app-config\/(.*)$/,
                    replacement: `${shellAppConfigRoot}/$1`,
                },
                {
                    find: /^@shell-app-providers$/,
                    replacement: path.resolve(shellAppProvidersRoot, 'index.ts'),
                },
                {
                    find: /^@shell-app-providers\/(.*)$/,
                    replacement: `${shellAppProvidersRoot}/$1`,
                },
                {
                    find: /^@shell-icons$/,
                    replacement: path.resolve(shellIconsRoot, 'index.ts'),
                },
                {
                    find: /^@shell-icons\/(.*)$/,
                    replacement: `${shellIconsRoot}/$1`,
                },
            ]
        }
    }
})
