import React from 'react'

import { Container, Header } from '@libra-shell/shared'

export interface CheckoutLayoutProps {
    children: React.ReactNode
}

export function CheckoutLayout({ children }: CheckoutLayoutProps) {
    return (
        <main className="min-h-screen bg-[#f2f6fa]">
            <Header className="bg-white" />
            <Container>{children}</Container>
        </main>
    )
}
