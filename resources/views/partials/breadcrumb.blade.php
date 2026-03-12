<div class="section-header-breadcrumb">
    @if(isset($linkTitle) && isset($title))
        <div class="breadcrumb-item"><a href="{{ $linkTitle }}">{{ $title }}</a></div>
    @endif
    @if(isset($linkSubpage) && isset($subpage))
        <div class="breadcrumb-item"><a href="{{ $linkSubpage }}">{{ $subpage }}</a></div>
    @endif
    @if(isset($linkPage) && isset($page))
        <div class="breadcrumb-item"><a href="{{ $linkPage }}">{{ $page }}</a></div>
    @endif
    @if(isset($content))
        <div class="breadcrumb-item">{{ $content }}</div>
    @endif
</div>