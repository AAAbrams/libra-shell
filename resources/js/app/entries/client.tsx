import '@shell-css-entry'

import { createInertiaApp } from '@inertiajs/react'
import { createRoot, hydrateRoot } from 'react-dom/client'

import {
    appName,
    pageLookupPaths,
    pages,
} from '@/js/app/config/page-registry'
import { AppProvider } from '@/js/app/providers/app-provider'
import { resolvePageComponent } from '@/js/lib'
import type { InertiaPage } from '@/js/types'

function resolveInitialPage(): InertiaPage | null {
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

const initialPage = resolveInitialPage()

if (!initialPage) {
    throw new Error('Inertia initial page payload not found in DOM.')
}

void createInertiaApp({
    page: initialPage as never,
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: async (name) =>
        (await resolvePageComponent(name, pages, pageLookupPaths)).default,
    setup({ el, App, props }) {
        if (el.hasChildNodes()) {
            hydrateRoot(
                el,
                <AppProvider>
                    <App {...props} />
                </AppProvider>
            )
            return
        }

        createRoot(el).render(
            <AppProvider>
                <App {...props} />
            </AppProvider>
        )
    },
    progress: {
        color: '#4B5563',
    },
})
