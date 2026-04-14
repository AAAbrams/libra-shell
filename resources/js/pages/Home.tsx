import { Head } from '@inertiajs/react'
import {
    Badge,
    Box,
    Container,
    Paper,
    Stack,
    Text,
    Title,
} from '@mantine/core'

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
                        </Stack>
                    </Paper>
                </Container>
            </Box>
        </>
    )
}
