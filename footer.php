<footer>
            <p> © 2024 Thales Alenia Space </p>
            <p> | </p>
            <p> © 2024 Université Nice Côte-d'Azur - IUT Réseaux et Télécommunications - Sophia Antipolis</p>
            <p> | </p>
            <p> © 2024 CHECKLIST - Groupe 6</p>
        </footer>
    </body>
</html>

<script>
    // Execute positionFooter function when DOM content is loaded
    window.addEventListener('DOMContentLoaded', (event) => {
        positionFooter();
    });

    // Execute positionFooter function when window is resized
    window.addEventListener('resize', () => {
        positionFooter();
    });

    // Function to position the footer based on content and viewport height
    function positionFooter() {
        const body = document.body;
        const html = document.documentElement;
        const footer = document.querySelector('footer');

        // Calculate the maximum height of content
        const height = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight);

        // Compare content height with viewport height
        if (height > window.innerHeight) {
            // If content height is greater than viewport height, set footer position to relative
            footer.style.position = 'relative';
        } else {
            // If content height fits within viewport, fix footer to the bottom of the viewport
            footer.style.position = 'fixed';
            footer.style.bottom = '0';
        }
    }
</script>