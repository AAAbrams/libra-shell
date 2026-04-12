import { Head } from '@inertiajs/react'
import { ArrowRight } from 'lucide-react'

import { Button } from '@libra-shell/ui'

export default function Home() {
    return (
        <>
            <Head title="Home" />

            <main className="min-h-screen bg-[radial-gradient(circle_at_top,_hsl(var(--primary)/0.18),_transparent_42%),linear-gradient(180deg,hsl(var(--background)),hsl(var(--muted)/0.45))] text-foreground">
                <div className="mx-auto flex min-h-screen w-full max-w-6xl items-center px-6 py-16">
                    <section className="grid gap-8 md:max-w-2xl">
                        <span className="inline-flex w-fit items-center rounded-full border border-border/60 bg-background/70 px-3 py-1 text-sm text-muted-foreground shadow-sm backdrop-blur">
                            Inertia + Vite + Tailwind + shadcn/ui
                        </span>

                        <div className="grid gap-4">
                            <h1 className="max-w-3xl text-4xl font-semibold tracking-tight text-balance sm:text-5xl">
                                Libra Shell front-end scaffold is ready.
                            </h1>
                            <p className="max-w-2xl text-lg leading-8 text-muted-foreground">
                                The project now uses a Laravel-style Inertia
                                entrypoint, Tailwind v4, and shadcn-compatible
                                UI primitives.
                            </p>
                        </div>

                        <div className="flex flex-wrap gap-3">
                            <Button size="lg" className="gap-2">
                                Open next step
                                <ArrowRight className="size-4" />
                            </Button>

                            <Button size="lg" variant="outline">
                                Review setup
                            </Button>
                        </div>
                    </section>
                </div>
            </main>
        </>
    )
}
