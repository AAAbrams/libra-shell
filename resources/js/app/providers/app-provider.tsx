import '@mantine/core/styles.css'

import type { PropsWithChildren } from 'react'
import { MantineProvider } from '@mantine/core'

import { mantineTheme } from '@/js/app/config/mantine-theme'

export function AppProvider({ children }: PropsWithChildren) {
    return (
        <MantineProvider
            defaultColorScheme="light"
            forceColorScheme="light"
            theme={mantineTheme}
        >
            {children}
        </MantineProvider>
    )
}
