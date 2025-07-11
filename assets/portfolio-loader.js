// Portfolio Projects Loader
// This script fetches projects from data/projects.json and displays them

class PortfolioLoader {
    constructor(containerId = 'projects') {
        this.containerId = containerId;
        this.projects = [];
        this.init();
    }

    async init() {
        try {
            await this.loadProjects();
            this.renderProjects();
        } catch (error) {
            console.error('Error loading projects:', error);
            this.showError();
        }
    }

    async loadProjects() {
        try {
            // Add cache-busting query param
            const cacheBuster = '?v=' + Date.now();
            // Try API endpoint first, fallback to direct JSON file
            let response = await fetch('api/projects.php' + cacheBuster);
            if (!response.ok) {
                // Fallback to direct JSON file
                response = await fetch('data/projects.json' + cacheBuster);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
            }
            this.projects = await response.json();
        } catch (error) {
            console.error('Failed to load projects:', error);
            this.projects = [];
        }
    }

    renderProjects() {
        const container = document.getElementById(this.containerId);
        if (!container) {
            console.error(`Container with id '${this.containerId}' not found`);
            return;
        }

        if (this.projects.length === 0) {
            container.innerHTML = '<p class="no-projects">No projects available at the moment.</p>';
            return;
        }

        const projectsHTML = this.projects.map(project => this.createProjectHTML(project)).join('');
        container.innerHTML = projectsHTML;

        // Add click handlers for modal functionality if needed
        this.addModalHandlers();
    }

    createProjectHTML(project) {
        return `
            <div class="portfolio-item" data-title="${this.escapeHtml(project.title)}" data-description="${this.escapeHtml(project.description)}">
                <img src="${this.escapeHtml(project.image)}" alt="${this.escapeHtml(project.title)}" loading="lazy">
                <div class="portfolio-caption">
                    <span>${this.escapeHtml(project.title)}</span>
                    <span>${this.getMonthFromDate(project.date_added)}</span>
                </div>
            </div>
        `;
    }

    addModalHandlers() {
        // Add click handlers for portfolio items if modal functionality exists
        const portfolioItems = document.querySelectorAll('.portfolio-item');
        portfolioItems.forEach(item => {
            item.addEventListener('click', () => {
                this.openModal(item);
            });
        });
    }

    openModal(item) {
        // Check if modal functionality exists on the page
        const modal = document.getElementById('portfolio-modal');
        if (modal) {
            const img = item.querySelector('img');
            const title = item.getAttribute('data-title');
            const description = item.getAttribute('data-description');
            
            const modalImg = document.getElementById('modal-img');
            const modalTitle = document.getElementById('modal-title');
            const modalDate = document.getElementById('modal-date');
            
            if (modalImg) modalImg.src = img.src;
            if (modalImg) modalImg.alt = img.alt;
            if (modalTitle) modalTitle.textContent = title;
            if (modalDate) modalDate.textContent = this.getMonthFromDate(item.querySelector('.portfolio-caption span:last-child').textContent);
            
            modal.style.display = 'flex';
        }
    }

    getMonthFromDate(dateString) {
        if (!dateString) return 'RECENT';
        
        try {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return 'RECENT';
            
            const months = [
                'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE',
                'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'
            ];
            return months[date.getMonth()];
        } catch (error) {
            return 'RECENT';
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showError() {
        const container = document.getElementById(this.containerId);
        if (container) {
            container.innerHTML = '<p class="error-message">Unable to load projects. Please try again later.</p>';
        }
    }

    // Method to refresh projects (useful for admin updates)
    async refresh() {
        await this.loadProjects();
        this.renderProjects();
    }
}

// Auto-initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize portfolio loader
    window.portfolioLoader = new PortfolioLoader('projects');
    
    // Add modal close functionality if modal exists
    const modalClose = document.getElementById('modal-close');
    const modalBackdrop = document.querySelector('.portfolio-modal-backdrop');
    const modal = document.getElementById('portfolio-modal');
    
    if (modalClose) {
        modalClose.addEventListener('click', () => {
            if (modal) modal.style.display = 'none';
        });
    }
    
    if (modalBackdrop) {
        modalBackdrop.addEventListener('click', () => {
            if (modal) modal.style.display = 'none';
        });
    }
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PortfolioLoader;
} 