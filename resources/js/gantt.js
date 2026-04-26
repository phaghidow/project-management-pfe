import Gantt from "frappe-gantt";

fetch("/api/tasks-gantt")
    .then(res => res.json())
    .then(tasks => {

        new Gantt("#gantt", tasks, {
            view_mode: 'Day',
            language: 'en'
        });

    });