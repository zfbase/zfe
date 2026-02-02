export function getAcEngine(settings = {}) {
  /** @type AbortController | null */
  let ac = null;
  const { minLength, sourceUrl, exclude } = settings;
  return async (query, _, async) => {
    try {
      if (ac) {
        ac.abort();
        ac = null;
      }
      let url = sourceUrl;
      if (query.length > 0 && query.length < minLength) {
        return;
      }
      const sp = new URLSearchParams();
      if (query.length > 0) {
        sp.set('term', query);
      }
      if (exclude) {
        const ids = typeof exclude === 'function' ? exclude() : exclude;
        if (ids.length > 0) {
          sp.set('exclude', ids.join(','));
        }
      }
      if (sp.size > 0) {
        url += `?${sp.toString()}`;
      }
      ac = new AbortController();
      const res = await fetch(url, { signal: ac.signal });
      const items = await res.json();
      async(items);
    } catch (err) {
      if (!ac || !ac.signal.aborted) {
        console.error(
          'Autocomplete failed',
          { sourceUrl, query, minLength, exclude },
          err,
        );
      }
      async([]);
    }
  };
}
