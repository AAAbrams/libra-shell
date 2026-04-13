import React from 'react'
import {
    Anchor,
    Box,
    Group,
    Image,
    Stack,
    Text,
} from '@mantine/core'

import { Container } from './container'

interface Props {
    className?: string
}
export const Header: React.FC<Props> = ({ className }) => {
    return (
        <Box
            className={className}
            component="header"
            style={{
                backgroundColor: '#ffffff',
                borderBottom: '1px solid rgba(148, 163, 184, 0.2)',
            }}
        >
            <Container>
                <Group justify="space-between" py="xl" wrap="wrap">
                    <Anchor href="/public" underline="never">
                        <Stack align="center" gap={6}>
                            <Image
                                alt="logo"
                                fit="contain"
                                h={52}
                                src="/local/templates/dslon_desktop/img/logo.svg"
                                w={262}
                            />
                            <Text c="dimmed" fz="sm" lh={1.2}>
                                Эксперт в уходе за полостью рта
                            </Text>
                        </Stack>
                    </Anchor>

                    <Text c="dark.7" fw={600} size="lg">
                        8 (499) 638-27-58
                    </Text>
                </Group>
            </Container>
        </Box>
    )
}
