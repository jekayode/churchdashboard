<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/biz') }}</loc>
        <changefreq>daily</changefreq>
    </url>
    @foreach($businesses as $business)
        <url>
            <loc>{{ url('/biz/'.$business->slug) }}</loc>
            <lastmod>{{ $business->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
        </url>
    @endforeach
</urlset>
