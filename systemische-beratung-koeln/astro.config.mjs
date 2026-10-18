import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';

// Externe Links in Markdown bekommen rel="noopener".
function rehypeExternalLinks() {
  const visit = (node) => {
    if (node.type === 'element' && node.tagName === 'a' && /^https?:\/\//.test(node.properties?.href ?? '')
      && !node.properties.href.startsWith('https://systemische-beratung-koeln.com')) {
      node.properties.rel = ['noopener'];
    }
    (node.children ?? []).forEach(visit);
  };
  return (tree) => visit(tree);
}

export default defineConfig({
  markdown: { rehypePlugins: [rehypeExternalLinks] },
  site: 'https://systemische-beratung-koeln.com',
  output: 'static',
  trailingSlash: 'always',
  compressHTML: true,
  build: { format: 'directory', inlineStylesheets: 'never' },
  integrations: [sitemap({ filter: (p) => !p.includes('/404') })],
});
