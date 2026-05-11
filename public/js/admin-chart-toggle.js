// ========================================
// ADMIN DASHBOARD CHART TOGGLE SYSTEM
// ========================================

// Chart instances storage
let adminDashboardCharts = {};
let adminChartTypes = {
    'classRevenueChart': 'line',
    'classCountChart': 'bar',
    'certificationRevenueChart': 'line'
};

// Generic chart type switcher
function switchAdminChartType(chartName, newType) {
    if (adminChartTypes[chartName] === newType) return;
    
    // Destroy old chart if exists
    if (adminDashboardCharts[chartName]) {
        adminDashboardCharts[chartName].destroy();
    }
    
    // Update type
    adminChartTypes[chartName] = newType;
    
    // Recreate chart
    createAdminChart(chartName, newType);
    
    // Update button styles
    updateAdminChartTypeButtons(chartName, newType);
}

function updateAdminChartTypeButtons(chartName, currentType) {
    const barBtn = document.getElementById(chartName + 'TypeBar');
    const lineBtn = document.getElementById(chartName + 'TypeLine');
    
    if (barBtn && lineBtn) {
        if (currentType === 'bar') {
            barBtn.classList.remove('bg-gray-100', 'text-gray-600', 'border-gray-200');
            barBtn.classList.add('bg-emerald-100', 'text-emerald-700', 'border-emerald-400');
            lineBtn.classList.remove('bg-emerald-100', 'text-emerald-700', 'border-emerald-400');
            lineBtn.classList.add('bg-gray-100', 'text-gray-600', 'border-gray-200');
        } else {
            lineBtn.classList.remove('bg-gray-100', 'text-gray-600', 'border-gray-200');
            lineBtn.classList.add('bg-emerald-100', 'text-emerald-700', 'border-emerald-400');
            barBtn.classList.remove('bg-emerald-100', 'text-emerald-700', 'border-emerald-400');
            barBtn.classList.add('bg-gray-100', 'text-gray-600', 'border-gray-200');
        }
    }
}

// For backward compatibility - this function is used by onclick handlers
function switchChartType(chartName, newType) {
    switchAdminChartType(chartName, newType);
}

function createAdminChart(chartName, type) {
    // Chart implementation will be in the Blade file with dynamic data
    // This is a placeholder that gets called after data is available
    console.log(`Creating ${chartName} with type ${type}`);
}
