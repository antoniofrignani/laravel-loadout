<x-mail::message>
# Package suggestion received

`{{ $suggestion->composer_name }}` was added to a Laravel Loadout as a custom package.

**Display name:** {{ $suggestion->display_name }}

**Source:** {{ $suggestion->source_url }}

**Loadout:** {{ $suggestion->loadout?->slug ?? 'Draft without slug' }}

<x-mail::button :url="$approveUrl">
Approve package
</x-mail::button>

<x-mail::button :url="$ignoreUrl" color="error">
Ignore package
</x-mail::button>

If approved, the package becomes available in the curated catalog. If ignored, this suggestion is marked as reviewed and excluded.
</x-mail::message>
