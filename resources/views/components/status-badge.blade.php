@props(['status'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset '.$status->badgeClasses()]) }}>
    {{ $status->label() }}
</span>
