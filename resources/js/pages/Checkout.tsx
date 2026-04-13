import { Head } from '@inertiajs/react'
import { Stack, Text, Title } from '@mantine/core'

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
                <Stack gap="sm" py="xl">
                    <Title order={1}>{seo.h1}</Title>
                    <Text c="dimmed" maw={640}>
                        {seo.description}
                    </Text>
                </Stack>
            </CheckoutLayout>
        </>
    )
}
