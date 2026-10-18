import { defineCollection, z } from 'astro:content';
import { glob } from 'astro/loaders';

const pages = defineCollection({
  loader: glob({ pattern: '**/*.md', base: './src/content/pages' }),
  schema: z.object({
    title: z.string().max(60),
    description: z.string().min(140).max(155),
    h1: z.string(),
    updated: z.coerce.date(),
    published: z.coerce.date(),
    kurz: z.string(),
    schemaType: z.enum(['Article', 'WebPage']).default('Article'),
    faq: z.array(z.object({ q: z.string(), a: z.string() })).default([]),
    sources: z.array(z.object({ label: z.string(), url: z.string().url() })).default([]),
    related: z.array(z.object({ href: z.string(), label: z.string() })).default([]),
    breadcrumb: z.string().optional(),
  }),
});

export const collections = { pages };
