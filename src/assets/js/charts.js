google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawChart);

function drawChart() {

    var data = google.visualization.arrayToDataTable([
        ['Task', 'Hours per Day'],
        ['Work',     11],
        ['Eat',      2],
        ['Commute',  2],
        ['Watch TV', 2],
        ['Sleep',    7]
    ]);
    var options = {
        legend: {alignment: 'end'},
        pieHole: 0.4,
        width: '500px',
        backgroundColor: '#000'
    };
    var chart = new google.visualization.PieChart(document.getElementById('collectionGraph'));

    chart.draw(data, options);
}