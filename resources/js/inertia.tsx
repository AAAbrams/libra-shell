import { createInertiaApp } from '@inertiajs/react'
import type { ComponentType, ReactElement } from 'react'
import { createRoot, hydrateRoot } from 'react-dom/client'

import { resolvePageComponent } from '@/js/lib/inertia'
import type { InertiaPage } from '@/js/types/inertia'

export type { InertiaPage }
export { resolvePageComponent }

export type InertiaPageModule = {
    default: ComponentType<any>
}

export type InertiaPageResolver = Record<string, () => Promise<InertiaPageModule>>

export type InertiaAppWrapper = (app: ReactElement) => ReactElement

export interface InertiaBootstrapOptions {
    appName?: string
    pageLookupPaths?: string[]
    pages: InertiaPageResolver
    progress?: {
        color?: string
        delay?: number
        includeCSS?: boolean
        showSpinner?: boolean
    }
    wrap?: InertiaAppWrapper
}

export interface InertiaClientBootstrapOptions extends InertiaBootstrapOptions {
    page?: InertiaPage | null
}

export interface InertiaServerBootstrapOptions extends InertiaBootstrapOptions {
    port?: number
}

export function resolveInitialPage(): InertiaPage | null {
    const scriptElement = document.querySelector<HTMLScriptElement>(
        'script[data-page="app"][type="application/json"]'
    )

    if (scriptElement?.textContent) {
        return JSON.parse(scriptElement.textContent) as InertiaPage
    }

    const legacyRootElement = document.getElementById('app')
    const legacyPage = legacyRootElement?.dataset.page

    if (legacyPage) {
        return JSON.parse(legacyPage) as InertiaPage
    }

    return null
}

export function createLibraInertiaClient(
    options: InertiaClientBootstrapOptions
) {
    const initialPage = options.page ?? resolveInitialPage()

    if (!initialPage) {
        throw new Error('Inertia initial page payload not found in DOM.')
    }

    return createInertiaApp({
        page: initialPage as never,
        progress: options.progress ?? {
            color: '#4B5563',
        },
        resolve: async (name) =>
            (await resolvePageComponent(
                name,
                options.pages,
                options.pageLookupPaths
            )).default,
        setup: ({ el, App, props }) => {
            const application = wrapApplication(<App {...props} />, options.wrap)

            if (el.hasChildNodes()) {
                hydrateRoot(el, application)
                return
            }

            createRoot(el).render(application)
        },
        title: buildDocumentTitle(options.appName),
    })
}

function buildDocumentTitle(appName = 'Libra Shell') {
    return (title: string) => (title ? `${title} - ${appName}` : appName)
}

function wrapApplication(
    application: ReactElement,
    wrap?: InertiaAppWrapper
): ReactElement {
    if (!wrap) {
        return application
    }

    return wrap(application)
}
