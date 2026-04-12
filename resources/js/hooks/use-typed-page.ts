import { usePage } from '@inertiajs/react'

export function useTypedPage<T>() {
    return usePage<{} & T>()
}
