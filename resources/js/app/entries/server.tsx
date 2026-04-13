import '@shell-css-entry'

import { createInertiaApp } from '@inertiajs/react'
import createServer from '@inertiajs/react/server'
import ReactDOMServer from 'react-dom/server'

import {
    appName,
    pageLookupPaths,
    pages,
} from '@/js/app/config/page-registry'
import { resolvePageComponent } from '@/js/lib'

const ssrPort = Number(import.meta.env.VITE_SSR_PORT || 13714)

void createServer((page) =>
    createInertiaApp({
        page,
        render: ReactDOMServer.renderToString,
        title: (title) => (title ? `${title} - ${appName}` : appName),
        resolve: async (name) =>
            (await resolvePageComponent(name, pages, pageLookupPaths)).default,
        setup: ({ App, props }) => <App {...props} />,
    }),
    ssrPort
)
