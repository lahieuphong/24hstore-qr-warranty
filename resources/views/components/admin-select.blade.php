@props([
    'id',
    'label',
    'model',
    'value' => null,
    'options' => [],
    'live' => false,
])

@php
    $normalizedOptions = collect($options);
    $optionsAreList = array_is_list($normalizedOptions->all());

    $items = $normalizedOptions
        ->map(fn ($optionLabel, $optionValue) => [
            'key' => (string) ($optionsAreList ? $optionLabel : $optionValue),
            'value' => $optionsAreList ? $optionLabel : $optionValue,
            'label' => (string) $optionLabel,
        ])
        ->values()
        ->all();

    $initialIndex = collect($items)->search(
        fn (array $item): bool => $item['key'] === (string) $value,
    );
    $initialIndex = $initialIndex === false ? 0 : $initialIndex;
    $initialLabel = $items[$initialIndex]['label'] ?? '';
@endphp

<div
    {{ $attributes->class('admin-select') }}
    x-data="{
        open: false,
        placement: 'bottom',
        selected: $wire.$entangle(@js($model), @js($live)),
        activeIndex: @js($initialIndex),
        items: @js($items),
        get selectedLabel() {
            return this.items.find((item) => this.isSelected(item))?.label ?? '';
        },
        isSelected(item) {
            return String(this.selected) === item.key;
        },
        optionElements() {
            return Array.from(this.$refs.listbox.querySelectorAll('[role=option]'));
        },
        positionMenu() {
            const triggerRect = this.$refs.trigger.getBoundingClientRect();
            const menuHeight = Math.min(this.$refs.listbox.scrollHeight, 288);
            const spaceBelow = window.innerHeight - triggerRect.bottom - 12;
            const spaceAbove = triggerRect.top - 12;

            this.placement = spaceBelow < menuHeight && spaceAbove > spaceBelow ? 'top' : 'bottom';
        },
        focusAt(index) {
            const options = this.optionElements();

            if (options.length === 0) {
                return;
            }

            this.activeIndex = (index + options.length) % options.length;
            options[this.activeIndex].focus();
            options[this.activeIndex].scrollIntoView({ block: 'nearest' });
        },
        openMenu(fromEnd = false) {
            this.open = true;
            this.$nextTick(() => {
                const selectedIndex = this.items.findIndex((item) => this.isSelected(item));
                this.positionMenu();
                this.focusAt(fromEnd ? this.items.length - 1 : Math.max(selectedIndex, 0));
            });
        },
        toggleMenu() {
            this.open ? this.close() : this.openMenu();
        },
        move(step) {
            this.focusAt(this.activeIndex + step);
        },
        close(returnFocus = true) {
            this.open = false;

            if (returnFocus) {
                this.$nextTick(() => this.$refs.trigger.focus());
            }
        },
        choose(item) {
            this.selected = item.value;
            this.open = false;
            this.$nextTick(() => this.$refs.trigger.focus());
        },
    }"
    x-on:click.outside="if (open) close(false)"
    x-on:resize.window="if (open) positionMenu()"
>
    <span id="{{ $id }}-label" class="sr-only">{{ $label }}</span>

    <button
        id="{{ $id }}"
        x-ref="trigger"
        type="button"
        class="admin-select-trigger"
        x-bind:class="{ 'border-rose-600 ring-2 ring-rose-200': open }"
        x-bind:aria-expanded="open"
        aria-haspopup="listbox"
        aria-controls="{{ $id }}-listbox"
        aria-labelledby="{{ $id }}-label {{ $id }}-value"
        x-on:click="toggleMenu()"
        x-on:keydown.arrow-down.prevent="open ? move(1) : openMenu()"
        x-on:keydown.arrow-up.prevent="open ? move(-1) : openMenu(true)"
        x-on:keydown.escape.prevent="if (open) close()"
        wire:loading.attr="disabled"
        wire:target="{{ $model }}"
    >
        <span id="{{ $id }}-value" class="truncate" x-text="selectedLabel">{{ $initialLabel }}</span>
        <x-lucide-chevron-down
            class="size-4 shrink-0 text-slate-500 transition-transform duration-150"
            x-bind:class="{ 'rotate-180 text-rose-700': open }"
            aria-hidden="true"
        />
    </button>

    <div
        id="{{ $id }}-listbox"
        x-ref="listbox"
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 -translate-y-1 scale-[0.98]"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 -translate-y-1 scale-[0.98]"
        class="admin-select-menu"
        x-bind:class="{
            'top-full mt-2 origin-top': placement === 'bottom',
            'bottom-full mb-2 origin-bottom': placement === 'top',
        }"
        role="listbox"
        aria-labelledby="{{ $id }}-label"
        x-on:keydown.arrow-down.prevent.stop="move(1)"
        x-on:keydown.arrow-up.prevent.stop="move(-1)"
        x-on:keydown.home.prevent.stop="focusAt(0)"
        x-on:keydown.end.prevent.stop="focusAt(items.length - 1)"
        x-on:keydown.enter.prevent.stop="choose(items[activeIndex])"
        x-on:keydown.space.prevent.stop="choose(items[activeIndex])"
        x-on:keydown.escape.prevent.stop="close()"
        x-on:keydown.tab="open = false"
    >
        <template x-for="(item, index) in items" x-bind:key="item.key">
            <button
                type="button"
                role="option"
                tabindex="-1"
                class="admin-select-option"
                x-bind:aria-selected="isSelected(item)"
                x-bind:class="{
                    'bg-rose-100 font-semibold text-rose-900': isSelected(item),
                    'bg-rose-50 text-rose-900': activeIndex === index && !isSelected(item),
                }"
                x-on:focus="activeIndex = index"
                x-on:click.prevent.stop="choose(item)"
            >
                <span class="flex size-4 shrink-0 items-center justify-center text-rose-700" aria-hidden="true">
                    <x-lucide-check class="size-4" x-show="isSelected(item)" />
                </span>
                <span class="truncate" x-text="item.label"></span>
            </button>
        </template>
    </div>
</div>
