@if($layouts)
  @foreach($layouts as $layout)
    @php echo $layout->render(); @endphp
  @endforeach
@endif