import { Head } from '@inertiajs/react'

import { useTypedPage } from '@/js/hooks'
import { CheckoutLayout } from '@/js/layouts'

type Props = {
    seo: {
        title: string
        description: string
        h1: string
    }
}

export default function Checkout() {
    const { seo }: Props = useTypedPage<Props>().props

    return (
        <>
            <Head>
                <title>{seo.title}</title>
            </Head>
            <CheckoutLayout>
                <h1>{seo.h1}</h1>
            </CheckoutLayout>
        </>
    )
}
