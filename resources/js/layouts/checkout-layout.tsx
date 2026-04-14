import React from 'react'
import { Box } from '@mantine/core'

import { Container, Header } from '@/js/components/shared'

export interface CheckoutLayoutProps {
    children: React.ReactNode
}

export function CheckoutLayout({ children }: CheckoutLayoutProps) {
    return (
        <Box
            component="main"
            style={{
                backgroundColor: '#f2f6fa',
                minHeight: '100vh',
            }}
        >
            <Header />
            <Container>{children}</Container>
        </Box>
    )
}
