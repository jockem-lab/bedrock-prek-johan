@if($objectFacts['documents'])
  <div class="col-span-12">
    <x-accordion title="Dokument" group="descriptions_facts" id="documents">
      <ul class="document-links">
        @foreach($objectFacts['documents'] as $document)
          <li><a href="{{ $document->href }}" target="_blank"><img src="@asset('images/document.svg')" alt=""><span>{{ $document->alias }}</span></a></li>
        @endforeach
      </ul>
    </x-accordion>
  </div>
@endif