// Pure JavaScript Circular Nutrition Meter
function createNutritionMeter() {
    // CSS styles for the nutrition meter
    const style = document.createElement("style")
    style.textContent = `
      .nutrition-meter-container {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        padding: 1rem;
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: var(--card-shadow);
        margin-bottom: 1.5rem;
      }
      
      .nutrition-meter-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        margin-bottom: 1rem;
      }
      
      .nutrition-meter-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
      }
      
      .nutrition-status {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        color: white;
        font-weight: 500;
        font-size: 0.875rem;
      }
      
      .nutrition-meters {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 1rem;
        width: 100%;
      }
      
      .circular-meter {
        display: flex;
        flex-direction: column;
        align-items: center;
      }
      
      .circular-meter-name {
        font-weight: 600;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
      }
      
      .circular-meter-svg-container {
        position: relative;
        width: 100px;
        height: 100px;
      }
      
      .circular-meter-svg {
        transform: rotate(-90deg);
        width: 100%;
        height: 100%;
      }
      
      .circular-meter-background {
        fill: none;
        stroke: #e5e7eb;
        stroke-width: 8;
      }
      
      .circular-meter-progress {
        fill: none;
        stroke-width: 8;
        stroke-linecap: round;
        transition: stroke-dashoffset 1s ease-in-out;
      }
      
      .circular-meter-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
      }
      
      .circular-meter-value {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        line-height: 1;
      }
      
      .circular-meter-unit {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.25rem;
      }
      
      .circular-meter-icon {
        margin-top: 0.5rem;
        font-size: 1.25rem;
      }
      
      .text-success { color: #10b981; }
      .text-danger { color: #ef4444; }
      .text-warning { color: #f59e0b; }
      .text-purple { color: #8b5cf6; }
      
      .stroke-success { stroke: #10b981; }
      .stroke-danger { stroke: #ef4444; }
      .stroke-warning { stroke: #f59e0b; }
      .stroke-purple { stroke: #8b5cf6; }
      .stroke-secondary { stroke: #9ca3af; }
      
      .bg-success { background-color: #10b981; }
      .bg-danger { background-color: #ef4444; }
      .bg-warning { background-color: #f59e0b; }
      .bg-purple { background-color: #8b5cf6; }
      
      @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
      }
      
      .animate-pulse {
        animation: pulse 2s infinite;
      }
    `
    document.head.appendChild(style)
  
    // Function to update the nutrition meter
    window.updateNutritionMeter = (dailyTotals) => {
      const nutrients = [
        { name: "Calories", current: dailyTotals.calories, minimum: 2000, maximum: 2500, unit: "kcal" },
        { name: "Carbs", current: dailyTotals.carbs, minimum: 225, maximum: 300, unit: "g" },
        { name: "Protein", current: dailyTotals.protein, minimum: 60, maximum: 150, unit: "g" },
        { name: "Fat", current: dailyTotals.fat, minimum: 44, maximum: 78, unit: "g" },
      ]
  
      // Determine overall status
      const anyExcessive = nutrients.some((n) => n.current > n.maximum)
      const allSufficient = nutrients.every((n) => n.current >= n.minimum)
      const highProtein = nutrients.find((n) => n.name === "Protein")?.current >= 120
  
      let statusLabel = ""
      let statusColor = ""
      let statusIcon = ""
  
      if (anyExcessive) {
        statusLabel = "Cheat Day"
        statusColor = "bg-purple"
        statusIcon = '<i class="bi bi-emoji-sunglasses"></i>'
      } else if (allSufficient && highProtein) {
        statusLabel = "Muscle Day"
        statusColor = "bg-warning"
        statusIcon = '<i class="bi bi-lightning"></i>'
      } else if (allSufficient) {
        statusLabel = "Balanced Diet"
        statusColor = "bg-success"
        statusIcon = '<i class="bi bi-check-circle"></i>'
      } else {
        statusLabel = "Insufficient"
        statusColor = "bg-danger"
        statusIcon = '<i class="bi bi-x-circle"></i>'
      }
  
      // Create the container
      const container = document.getElementById("nutrition-meter-container")
      container.className = "nutrition-meter-container"
      container.innerHTML = ""
  
      // Create header
      const header = document.createElement("div")
      header.className = "nutrition-meter-header"
      header.innerHTML = `
        <h3 class="nutrition-meter-title">Daily Nutrition</h3>
        <span class="nutrition-status ${statusColor}">${statusLabel}</span>
      `
      container.appendChild(header)
  
      // Create meters container
      const metersContainer = document.createElement("div")
      metersContainer.className = "nutrition-meters"
      container.appendChild(metersContainer)
  
      // Create individual meters
      nutrients.forEach((nutrient) => {
        const percentage = Math.min(100, (nutrient.current / nutrient.minimum) * 100)
  
        // Determine color based on value
        let meterColor = "stroke-secondary"
        let textColor = "text-secondary"
        let icon = ""
  
        if (nutrient.current >= nutrient.minimum) {
          meterColor = "stroke-success"
          textColor = "text-success"
          icon = '<i class="bi bi-check-circle text-success"></i>'
        } else if (nutrient.current >= nutrient.minimum * 0.7) {
          meterColor = "stroke-warning"
          textColor = "text-warning"
        } else {
          meterColor = "stroke-danger"
          textColor = "text-danger"
          icon = '<i class="bi bi-x-circle text-danger"></i>'
        }
  
        if (nutrient.current > nutrient.maximum) {
          meterColor = "stroke-purple"
          textColor = "text-purple"
          icon = '<i class="bi bi-emoji-sunglasses text-purple"></i>'
        }
  
        // Special case for protein
        if (nutrient.name === "Protein" && nutrient.current >= 120) {
          meterColor = "stroke-warning"
          textColor = "text-warning"
          icon = '<i class="bi bi-lightning text-warning"></i>'
        }
  
        // SVG parameters
        const size = 100
        const strokeWidth = 8
        const radius = (size - strokeWidth) / 2
        const circumference = 2 * Math.PI * radius
        const strokeDashoffset = circumference - (percentage / 100) * circumference
  
        const meterElement = document.createElement("div")
        meterElement.className = "circular-meter"
        meterElement.innerHTML = `
          <div class="circular-meter-name">${nutrient.name}</div>
          <div class="circular-meter-svg-container">
            <svg class="circular-meter-svg" viewBox="0 0 ${size} ${size}">
              <circle class="circular-meter-background" cx="${size / 2}" cy="${size / 2}" r="${radius}"></circle>
              <circle class="circular-meter-progress ${meterColor}" cx="${size / 2}" cy="${size / 2}" r="${radius}" 
                      stroke-dasharray="${circumference}" stroke-dashoffset="${strokeDashoffset}"></circle>
            </svg>
            <div class="circular-meter-text">
              <div class="circular-meter-value ${textColor}">${Math.round(percentage)}</div>
              <div class="circular-meter-unit">${nutrient.current}/${nutrient.minimum} ${nutrient.unit}</div>
            </div>
          </div>
          <div class="circular-meter-icon">${icon}</div>
        `
  
        metersContainer.appendChild(meterElement)
      })
    }
  }
  
  // Initialize the nutrition meter when the DOM is loaded
  document.addEventListener("DOMContentLoaded", createNutritionMeter)
  