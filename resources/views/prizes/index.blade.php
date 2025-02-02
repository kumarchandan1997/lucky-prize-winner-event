@extends('default')

@section('content')


    @include('prob-notice')


    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('prizes.create') }}" class="btn btn-info">Create</a>
                </div>
                <h1>Prizes</h1>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Title</th>
                            <th>Probability</th>
                            <th>Awarded</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($prizes as $prize)
                            <tr>
                                <td>{{ $prize->id }}</td>
                                <td>{{ $prize->title }}</td>
                                <td>{{ $prize->probability }}</td>
                                <td>{{ $prize->awarded_people }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('prizes.edit', [$prize->id]) }}" class="btn btn-primary">Edit</a>
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['prizes.destroy', $prize->id]]) !!}
                                        {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                                        {!! Form::close() !!}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-header">
                        <h3>Simulate</h3>
                    </div>
                    <div class="card-body">
                        {!! Form::open(['method' => 'POST', 'route' => ['simulate']]) !!}
                        <div class="form-group">
                            {!! Form::label('number_of_prizes', 'Number of Prizes') !!}
                            {!! Form::number('number_of_prizes', $numberOfPeople, ['class' => 'form-control']) !!}
                        </div>
                        {!! Form::submit('Simulate', ['class' => 'btn btn-primary']) !!}
                        {!! Form::close() !!}
                    </div>

                    <br>

                    <div class="card-body">
                        {!! Form::open(['method' => 'POST', 'route' => ['reset']]) !!}
                        <input type="hidden" id="actualRewardData" value="{{ json_encode($actualReward) }}">
                        {!! Form::submit('Reset', ['class' => 'btn btn-primary']) !!}
                        {!! Form::close() !!}
                    </div>

                </div>
            </div>
        </div>
    </div>



    <div class="container  mb-4">
        <div class="row">
            <div class="col-md-6">
                <h2>Probability Settings</h2>
                <canvas id="probabilityChart"></canvas>
            </div>
            <div class="col-md-6">
                <h2>Actual Rewards</h2>
                <canvas id="awardedChart"></canvas>
            </div>
        </div>
    </div>


@stop


@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <script>
   document.addEventListener("DOMContentLoaded", function () {
    let prizes = @json($prizes);

    let probabilityLabels = prizes.map(prize => prize.title);
    let probabilityData = prizes.map(prize => parseFloat(prize.probability));

    // Generate random colors for each title
    let probabilityBackgroundColors = probabilityLabels.map(() => {
        return `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 0.7)`;
    });

    let ctx1 = document.getElementById("probabilityChart").getContext("2d");

    new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: probabilityLabels,
            datasets: [{
                data: probabilityData,
                backgroundColor: probabilityBackgroundColors,
                borderColor: "#ffffff",
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        generateLabels: function(chart) {
                            const original = Chart.overrides.doughnut.plugins.legend.labels.generateLabels;
                            const labels = original.call(this, chart);
                            labels.forEach(function(label, i) {
                                label.text = `${probabilityLabels[i]} (${probabilityData[i].toFixed(2)}%)`;
                            });
                            return labels;
                        }
                    }
                },
                datalabels: {
                    color: '#000',
                    font: {
                        weight: 'bold'
                    },
                    formatter: (value, ctx) => {
                        let total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        let percentage = ((value / total) * 100).toFixed(2) + "%";
                        let title = ctx.chart.data.labels[ctx.dataIndex];
                        return `${title} (${percentage})`;
                    },
                    anchor: 'center',
                    align: 'center',
                    display: true,
                }
            }
        }
    });

    // Actual Rewards chart (for actual percentage)
    let actualRewardData = JSON.parse(document.getElementById("actualRewardData").value);
    console.log(actualRewardData);

    let actualRewardLabels = actualRewardData.map(reward => reward.title);
    let actualRewardPercentages = actualRewardData.map(reward => parseFloat(reward.actual_percentage));

    // Generate random colors for each title (same colors for consistency)
    let actualRewardBackgroundColors = actualRewardLabels.map(() => {
        return `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 0.7)`;
    });

    let ctx2 = document.getElementById("awardedChart").getContext("2d");

    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: actualRewardLabels,
            datasets: [{
                data: actualRewardPercentages,
                backgroundColor: actualRewardBackgroundColors,
                borderColor: "#ffffff",
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        generateLabels: function(chart) {
                            const original = Chart.overrides.doughnut.plugins.legend.labels.generateLabels;
                            const labels = original.call(this, chart);
                            labels.forEach(function(label, i) {
                                label.text = `${actualRewardLabels[i]} (${actualRewardPercentages[i].toFixed(2)}%)`;
                            });
                            return labels;
                        }
                    }
                },
                datalabels: {
                    color: '#000',
                    font: {
                        weight: 'bold'
                    },
                    formatter: (value, ctx) => {
                        let total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        let percentage = ((value / total) * 100).toFixed(2) + "%";
                        let title = ctx.chart.data.labels[ctx.dataIndex];
                        return `${title} (${percentage})`;
                    },
                    anchor: 'center',
                    align: 'center',
                    display: true,
                }
            }
        }
    });
});

</script>

@endpush
