<div class="row">
    @foreach($cards ?? [] as $card)
        <div class="col-6 col-md-4 col-xl-3 mb-3">
            <div class="card card-stats card-round h-100">
                <div class="card-body py-3">
                    <p class="text-muted mb-1 small">{{ $card['label'] }}</p>
                    <h4 class="fw-bold mb-0">{{ $card['value'] }}</h4>
                </div>
            </div>
        </div>
    @endforeach
</div>
