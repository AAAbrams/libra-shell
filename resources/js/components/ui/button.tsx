import { forwardRef, type CSSProperties } from 'react'
import {
    Button as MantineButton,
    type ButtonProps as MantineButtonProps,
} from '@mantine/core'

export interface ButtonProps
    extends Omit<MantineButtonProps, 'size' | 'variant' | 'radius'> {
    size?: 'default' | 'sm' | 'lg' | 'icon'
    variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link'
}

const sizeMap = {
    default: 'md',
    sm: 'sm',
    lg: 'lg',
    icon: 'compact-md',
} as const

const variantMap = {
    default: 'filled',
    destructive: 'filled',
    outline: 'outline',
    secondary: 'light',
    ghost: 'subtle',
    link: 'transparent',
} as const

export const Button = forwardRef<HTMLButtonElement, ButtonProps>(
    (
        {
            color,
            size = 'default',
            style,
            variant = 'default',
            ...props
        },
        ref
    ) => {
        const resolvedVariant = variantMap[variant]
        const resolvedColor = color ?? (variant === 'destructive' ? 'red' : undefined)
        const resolvedStyle: CSSProperties = {
            ...(size === 'icon'
                ? {
                    width: 42,
                    minWidth: 42,
                    paddingInline: 0,
                }
                : {}),
            ...(variant === 'link'
                ? {
                    paddingInline: 0,
                }
                : {}),
            ...(style as CSSProperties | undefined),
        }

        return (
            <MantineButton
                color={resolvedColor}
                radius="xl"
                ref={ref}
                size={sizeMap[size]}
                style={resolvedStyle}
                variant={resolvedVariant}
                {...props}
            />
        )
    }
)

Button.displayName = 'Button'
