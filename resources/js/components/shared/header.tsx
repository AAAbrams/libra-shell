import React from 'react'

import { cn } from '@/js/lib'
import { Container } from './container'

interface Props {
    className?: string
}
export const Header: React.FC<Props> = ({ className }) => {
    return (
        <header className={cn('border-b', className)}>
            <Container className="flex items-center justify-between py-8">
                {/* left */}
                <a href="/public" className="flex flex-col items-center">
                    <img src="/local/templates/dslon_desktop/img/logo.svg" alt="logo" width={262} />
                    <p className="text-black-400 text-sm leading-3">Эксперт в уходе за полостью рта</p>
                </a>
                {/* right */}
                <div className="flex items-center gap-3">8 (499) 638-27-58</div>
            </Container>
        </header>
    )
}
