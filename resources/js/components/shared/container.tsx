import React from 'react'

import { cn } from '@/js/lib'

interface Props {
    className?: string
}

export const Container: React.FC<React.PropsWithChildren<Props>> = ({ className, children }) => {
    return (
        <div className={cn('mx-auto max-w-7xl', className)}>{children}</div>
    )
}
