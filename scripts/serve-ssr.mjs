import path from 'path'
import { pathToFileURL } from 'url'
import { fileURLToPath } from 'url'

const moduleRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..')
const defaultProjectRoot = resolveProjectRoot(moduleRoot)
const projectRoot = process.env.VITE_APP_ROOT?.trim()
    ? resolveOutputDir(process.env.VITE_APP_ROOT.trim(), defaultProjectRoot)
    : defaultProjectRoot
const ssrOutDir = process.env.VITE_SSR_OUT_DIR?.trim() || 'bootstrap/ssr'
const entryFile = path.resolve(resolveOutputDir(ssrOutDir, projectRoot), 'ssr.js')

await import(pathToFileURL(entryFile).href)

function resolveProjectRoot(currentModuleRoot) {
    if (currentModuleRoot.includes('/vendor/')) {
        return path.resolve(currentModuleRoot, '../../..')
    }

    return currentModuleRoot
}

function resolveOutputDir(outDir, projectRootPath) {
    if (!outDir) {
        return path.resolve(moduleRoot, 'public/build')
    }

    if (path.isAbsolute(outDir)) {
        return outDir
    }

    if (
        outDir === '.'
        || outDir.startsWith('./')
        || outDir.startsWith('../')
        || outDir.startsWith('bootstrap/')
    ) {
        return path.resolve(moduleRoot, outDir)
    }

    return path.resolve(projectRootPath, outDir)
}
