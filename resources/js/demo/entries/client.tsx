import '@shell-css-entry'

import { AppProvider } from '@/js/app/providers/app-provider'
import {
    appName,
    pageLookupPaths,
    pages,
} from '@/js/app/config/page-registry'
import { createLibraInertiaClient } from '@/js/inertia'

void createLibraInertiaClient({
    appName,
    pageLookupPaths,
    pages,
    wrap: (app) => (
        <AppProvider>
            {app}
        </AppProvider>
    ),
})
