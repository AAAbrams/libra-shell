export type InertiaPage = {
    component: string
    props: Record<string, unknown>
    url: string
    version: string | null
    flash?: Record<string, unknown>
    rememberedState?: Record<string, unknown>
}
