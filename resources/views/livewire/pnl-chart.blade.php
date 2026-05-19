<div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-white text-sm">P&L Acumulado</h2>
        <button wire:click="loadData" class="text-xs text-gray-500 hover:text-white transition px-2 py-1 rounded hover:bg-gray-800">
            Atualizar
        </button>
    </div>
    <div wire:ignore style="height: 220px;">
        <canvas id="pnl-chart-canvas" style="display:block;width:100%;height:100%"></canvas>
    </div>
</div>

@script
<script>
    let _pnlChart = null;

    function buildPnlChart(data) {
        const canvas = document.getElementById('pnl-chart-canvas');
        if (!canvas) return;

        if (_pnlChart) { _pnlChart.destroy(); _pnlChart = null; }

        const values = data.map(d => d.y);
        const lastVal = values.length ? values[values.length - 1] : 0;
        const color   = lastVal >= 0 ? '#4ade80' : '#f87171';

        _pnlChart = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: data.map(d => d.x),
                datasets: [{
                    label: 'P&L Acumulado (USDT)',
                    data: values,
                    borderColor: color,
                    backgroundColor: color + '18',
                    borderWidth: 2,
                    pointRadius: data.length < 30 ? 3 : 0,
                    pointHoverRadius: 5,
                    fill: true,
                    tension: 0.3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        borderColor: '#374151',
                        borderWidth: 1,
                        titleColor: '#9ca3af',
                        bodyColor: '#f3f4f6',
                        callbacks: {
                            label: ctx => {
                                const d = data[ctx.dataIndex];
                                const sign = d.pnl >= 0 ? '+' : '';
                                return [
                                    ` Acumulado: ${ctx.parsed.y.toFixed(4)} USDT`,
                                    d.pair ? ` Trade: ${d.pair} (${sign}${d.pnl} USDT)` : '',
                                ].filter(Boolean);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: '#6b7280', maxTicksLimit: 10, font: { size: 11 } },
                        grid:  { color: '#1f2937' },
                    },
                    y: {
                        ticks: {
                            color: '#6b7280',
                            font: { size: 11 },
                            callback: v => v.toFixed(2) + ' USDT'
                        },
                        grid: { color: '#1f2937' },
                    }
                }
            }
        });
    }

    buildPnlChart($wire.chartData);

    $wire.on('chart-data-updated', ({ data }) => buildPnlChart(data));
</script>
@endscript
