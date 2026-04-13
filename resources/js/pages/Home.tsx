import { Head } from '@inertiajs/react'
import {
    Badge,
    Box,
    Container,
    Group,
    Paper,
    Stack,
    Text,
    ThemeIcon,
    Title,
} from '@mantine/core'
import { ArrowRight } from 'lucide-react'

import { Button } from '@libra-shell/ui'

export default function Home() {
    return (
        <>
            <Head title="Home" />

            <Box component="main" mih="100vh">
                <Container py={96} size={1200}>
                    <Paper
                        p={{ base: 'xl', sm: 40 }}
                        radius="32px"
                        shadow="md"
                        style={{
                            background:
                                'linear-gradient(145deg, rgba(255, 255, 255, 0.92), rgba(248, 250, 252, 0.86))',
                            border: '1px solid rgba(255, 255, 255, 0.7)',
                            backdropFilter: 'blur(16px)',
                        }}
                    >
                        <Stack gap="xl" maw={720}>
                            <Badge
                                color="cyan"
                                radius="xl"
                                size="lg"
                                variant="light"
                                w="fit-content"
                            >
                                Inertia + Vite + Mantine
                            </Badge>

                            <Stack gap="md">
                                <Title order={1} size="3.5rem">
                                    Libra Shell front-end scaffold is ready.
                                </Title>

                                <Text c="dimmed" fz="lg" lh={1.7} maw={620}>
                                    The project now uses a Laravel-style
                                    Inertia entrypoint and Mantine UI
                                    primitives instead of Tailwind and shadcn.
                                </Text>
                            </Stack>

                            <Group gap="md" wrap="wrap">
                                <Button
                                    leftSection={
                                        <ThemeIcon color="cyan" radius="xl" size={28} variant="white">
                                            <ArrowRight size={16} />
                                        </ThemeIcon>
                                    }
                                    size="lg"
                                >
                                    Open next step
                                </Button>

                                <Button size="lg" variant="outline">
                                    Review setup
                                </Button>
                            </Group>
                        </Stack>
                    </Paper>
                </Container>
            </Box>
        </>
    )
}
