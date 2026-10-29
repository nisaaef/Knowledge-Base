@if ($author->website || $author->instagram || $author->linkedin)
    <ul class="flex flex-wrap gap-x-5 gap-y-2 text-sm font-semibold">
        @if ($author->website)
            <li><a href="{{ $author->website }}" target="_blank" rel="noopener noreferrer nofollow" class="text-cobalt-600 hover:underline">Situs web</a></li>
        @endif
        @if ($author->instagram)
            <li><a href="{{ $author->instagramUrl() }}" target="_blank" rel="noopener noreferrer nofollow" class="text-cobalt-600 hover:underline">Instagram &#64;{{ $author->instagram }}</a></li>
        @endif
        @if ($author->linkedin)
            <li><a href="{{ $author->linkedin }}" target="_blank" rel="noopener noreferrer nofollow" class="text-cobalt-600 hover:underline">LinkedIn</a></li>
        @endif
    </ul>
@endif
