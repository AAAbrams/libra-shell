type ClassNameValue =
    | string
    | number
    | boolean
    | null
    | undefined
    | ClassNameValue[]
    | Record<string, boolean | null | undefined>

function flattenClassName(input: ClassNameValue): string[] {
    if (!input) {
        return []
    }

    if (typeof input === 'string' || typeof input === 'number') {
        return [String(input)]
    }

    if (Array.isArray(input)) {
        return input.flatMap(flattenClassName)
    }

    if (typeof input === 'object') {
        return Object.entries(input)
            .filter(([, value]) => Boolean(value))
            .map(([key]) => key)
    }

    return []
}

export function cn(...inputs: ClassNameValue[]): string {
    return inputs.flatMap(flattenClassName).join(' ')
}
