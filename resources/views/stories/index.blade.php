<x-site-layout title="Stories">
    <section class="hero">
        <div>
            <p class="eyebrow">An archive of the unexplained</p>
            <h1>Every place has a story.<br><em>Some never leave you.</em></h1>
            <p class="intro">Empty houses. The last train home. A light where there shouldn't be one. Read the stories people bring back.</p>
            <a class="button" href="{{ route('userzone.stories.create') }}">Tell your story <span aria-hidden="true">↗</span></a>
        </div>
        <div class="hero-art" aria-hidden="true">
            <svg viewBox="0 0 300 340" xmlns="http://www.w3.org/2000/svg">
                <circle cx="188" cy="88" r="64" fill="#e6dec9"/>
                <path d="M35 290V140L88 84l53 56v150M17 155l71-78 70 78" fill="#233a32" stroke="#a9b6a0" stroke-width="2"/>
                <path d="M62 290V228q26-42 52 0v62" fill="#101e1b"/>
                <path d="M64 148h18v30H64zm31 0h18v30H95z" fill="#d4b574"/>
                <path d="M252 301V78m0 65-32-41m32 77 27-44m-27 86-40-40" fill="none" stroke="#71826b" stroke-width="5"/>
                <path d="M17 295q120-17 265 7M22 315q135-16 250 4" fill="none" stroke="#71826b" stroke-width="2"/>
                <path d="M158 253v-47a19 19 0 0 1 38 0v47l-10-7-9 7-9-7z" fill="#e7e6d9"/>
                <circle cx="171" cy="211" r="2.5" fill="#233a32"/><circle cx="183" cy="211" r="2.5" fill="#233a32"/>
            </svg>
            <span>Somewhere, after midnight.</span>
        </div>
    </section>
    <section class="archive">
        <div class="section-heading">
            <div><p class="eyebrow">Keep the lights on</p><h2>The latest whispers</h2></div>
            <span class="story-count">{{ $stories->total() }} {{ Str::plural('story', $stories->total()) }}</span>
        </div>
        <form method="GET" action="{{ route('stories.index') }}" class="archive-filters">
            <div class="field"><label for="q">Search stories</label><input name="q" id="q" value="{{ $filters['q'] }}" maxlength="80" placeholder="A title or a memory"></div>
            <div class="field"><label for="category">Category</label><select name="category" id="category"><option value="">All categories</option>@foreach(['ghost', 'urban_legend', 'conspiracy'] as $category)<option value="{{ $category }}" @selected($filters['category'] === $category)>{{ Str::headline($category) }}</option>@endforeach</select></div>
            <div class="field"><label for="tag">Tag</label><select name="tag" id="tag"><option value="">All tags</option>@foreach($tags as $tag)<option value="{{ $tag->id }}" @selected($filters['tag'] == $tag->id)>{{ $tag->name }}</option>@endforeach</select></div>
            <div class="field"><label for="country">Country</label><select name="country" id="country"><option value="">All countries</option>@foreach($geography->countries() as $code => $name)<option value="{{ $code }}" @selected($filters['country'] === $code)>{{ $name }}</option>@endforeach</select></div>
            <div class="field"><label for="sort">Sort by</label><select name="sort" id="sort"><option value="latest" @selected($filters['sort'] === 'latest')>Latest</option><option value="score" @selected($filters['sort'] === 'score')>Most vouched</option></select></div>
            <div class="form-actions"><button>Find stories</button><a href="{{ route('stories.index', ['reset' => 1]) }}">Reset filters</a></div>
        </form>
        @foreach($errors->all() as $error)<p class="error">{{ $error }}</p>@endforeach
        <div class="story-grid">
            @forelse($stories as $story)
                <x-story-card :story="$story" />
            @empty
                <div class="empty-state"><h3>A quiet night. For now.</h3><p>No stories yet. Be the first to share one.</p></div>
            @endforelse
        </div>
        {{ $stories->links() }}
    </section>
</x-site-layout>
