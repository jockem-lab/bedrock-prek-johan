<div class="coworker flex flex-col max-w-full">
  <x-link href="{{ $permalink }}">
    @if($type)
      <div class="type font-bold uppercase text-center text-sm tracking-widest mb-2 min-h-[2.5rem]">{{ $type }}</div>
    @endif
    <img class="mb-2" src="{{ $imageSrc }}" alt="{{ $name }}" loading="lazy"/>
    <div class="flex flex-col">
      <span class="name font-bold uppercase text-sm tracking-widest break-words">{{ $name }}</span>
      <span class="title text-sm">{{ $title }}</span>
      <span class="title-extra text-sm">{{ $titleExtra }}</span>
      @if($email)
        <x-link href="mailto:{{ $email }}" class="email text-sm break-words">{{ $email }}</x-link>
      @endif
    @if($cellphone)
        <x-link href="tel:{{ $cellphone }}">{{ $cellphoneString }}</x-link>
      @elseif($phone)
        <x-link href="tel:{{ $phone }}">{{ $phoneString }}</x-link>
      @endif
    </div>
  </x-link>
</div>
