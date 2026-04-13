import { createTheme } from '@mantine/core'

export const mantineTheme = createTheme({
    primaryColor: 'cyan',
    defaultRadius: 'xl',
    fontFamily: 'Manrope, Inter, ui-sans-serif, system-ui, sans-serif',
    headings: {
        fontFamily: 'Manrope, Inter, ui-sans-serif, system-ui, sans-serif',
    },
    colors: {
        cyan: [
            '#e1fbfb',
            '#c9f2f5',
            '#99e2ea',
            '#64d1df',
            '#3cc3d4',
            '#22bacb',
            '#13b5c7',
            '#00a0b1',
            '#008e9f',
            '#007b8a',
        ],
    },
    primaryShade: {
        light: 7,
        dark: 5,
    },
})
