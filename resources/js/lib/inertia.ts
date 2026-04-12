type PageModule = { default: React.ComponentType }
type PageResolver = Record<string, () => Promise<PageModule>>

function normalizeLookupPrefixes(prefixes: string[]): string[] {
    return prefixes
        .map((prefix) => prefix.trim().replace(/\/+$/g, ''))
        .filter(Boolean)
}

export async function resolvePageComponent(
    name: string,
    pages: PageResolver,
    lookupPrefixes: string[] = ['resources/js/pages']
): Promise<PageModule> {
    const prefixes = normalizeLookupPrefixes(lookupPrefixes)
    const candidates = prefixes.map((prefix) => `${prefix}/${name}.tsx`)
    const page = candidates
        .map((candidate) => pages[candidate])
        .find(Boolean)
        ?? Object.entries(pages).find(([candidate]) =>
            candidate.endsWith(`/${name}.tsx`)
        )?.[1]

    if (!page) {
        throw new Error(
            `Page not found: ${name}. Checked: ${candidates.join(', ')}`
        )
    }

    return page()
}
