import React from 'react'
import { Container as MantineContainer } from '@mantine/core'

interface Props {
    className?: string
    size?: string | number
}

export const Container: React.FC<React.PropsWithChildren<Props>> = ({
    children,
    className,
    size = 1240,
}) => {
    return (
        <MantineContainer className={className} px="md" size={size}>
            {children}
        </MantineContainer>
    )
}
