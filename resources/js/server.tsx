import { createInertiaApp } from '@inertiajs/react'
import createServer from '@inertiajs/react/server'
import type { ComponentType, ReactElement } from 'react'
import ReactDOMServer from 'react-dom/server'

import { resolvePageComponent } from '@/js/lib/inertia'
import type { InertiaPage } from '@/js/types/inertia'

export type InertiaPageModule = {
    default: ComponentType<any>
}

export type InertiaPageResolver = Record<string, () => Promise<InertiaPageModule>>

export type InertiaAppWrapper = (app: ReactElement) => ReactElement

export interface InertiaServerBootstrapOptions {
    appName?: string
    pageLookupPaths?: string[]
    pages: InertiaPageResolver
    port?: number
    wrap?: InertiaAppWrapper
}

export function createLibraInertiaServer(
    options: InertiaServerBootstrapOptions
) {
    return bootstrapLibraInertiaServer(options)
}

async function bootstrapLibraInertiaServer(
    options: InertiaServerBootstrapOptions
) {
    const port = options.port ?? Number(import.meta.env.VITE_SSR_PORT || 13714)

    return createServer(
        (page: InertiaPage) =>
            createInertiaApp({
                page: page as never,
                render: ReactDOMServer.renderToString,
                resolve: async (name) =>
                    (await resolvePageComponent(
                        name,
                        options.pages,
                        options.pageLookupPaths
                    )).default,
                setup: ({ App, props }) =>
                    wrapApplication(<App {...props} />, options.wrap),
                title: buildDocumentTitle(options.appName),
            }),
        port
    )
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
