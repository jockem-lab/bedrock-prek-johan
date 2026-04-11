@if($data && $data['class'])
  @if($data['class']['fluid'])
    <div class="{{ $data['class']['fluid'] }}" {!! $data['attributes']['fluid'] !!}>
  @endif
  @if($data['class']['wrapper'])
    <div class="{{ $data['class']['wrapper'] }}" {!! $data['attributes']['wrapper'] !!}>
  @endif
  @if($data['class']['container'])
    <div class="{{ $data['class']['container'] }}" {!! $data['attributes']['container'] !!}>
  @endif
  @if($data['class']['inner'])
    <div class="{{ $data['class']['inner'] }}" {!! $data['attributes']['inner'] !!}>
  @endif
@endif