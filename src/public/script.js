const headerRow = document.getElementById('table-header');
const tableBody = document.getElementById('table-body');
const searchInput = document.getElementById('search');
const toolFilter = document.getElementById('filter-tool');
const publishedFilter = document.getElementById('filter-published');
const authorFilter = document.getElementById('filter-author');

function renderValue(value, type) {
    switch (type) {
        case 'text':
        case 'select':
            return value.toString();
        case 'edit':
            return `<a class="btn btn-success" href="${value}">EDIT</a>`;
        case 'link':
            return `<a href="${value.link}">${value.title}</a>`;
    }

    return value;
}

function filter(pages, meta) {
    pages = searchInput.value === '' ? pages : searchBy(pages, meta, searchInput.value, Object.keys(meta));
    pages = toolFilter.value === '' ? pages : pages.filter((page) => page['tool'] === toolFilter.value);
    pages = authorFilter.value === '' ? pages : pages.filter((page) => page['author'] === authorFilter.value);

    if (publishedFilter.value === '') {
        return pages;
    } else if (publishedFilter.value === 'yes') {
        pages = pages.filter((page) => page['status'] === 'Published')
    } else if (publishedFilter.value === 'no') {
        pages = pages.filter((page) => page['status'] !== 'Published')
    }

    return pages;
}

function searchBy(pages, meta, search, fields) {
    if (search === '') return pages;
    function getSearchableValue(name, value) {
        value = value || '';
        switch (meta[name].type) {
            case 'text':
            case 'select':
                return value.toString().toLocaleLowerCase();
            case 'link':
                return value.title.toLocaleLowerCase();
        }

        return value.toLocaleLowerCase();
    }

    return pages.filter((page) => fields.some((f) => getSearchableValue(f, page[f]).toLocaleLowerCase().includes(search)));
}

function renderPages(pages, meta) {
    tableBody.innerHTML = '';
    for (const page of pages) {
        const row = document.createElement('tr');

        for (const name of Object.keys(meta)) {
            row.insertAdjacentHTML(
                'beforeend',
                `<td>${renderValue(page[name] || "", meta[name].type)}</td>`
            );
        }

        tableBody.appendChild(row);
    }
}

fetch('/pages').then((response) => response.json()).then((json) => {
    const meta = json.fields;

    for (const val of meta['tool'].values) {
        toolFilter.insertAdjacentHTML('beforeend', `<option value="${val}">${val}</option>`);
    }

    for (const val of meta['author'].values) {
        authorFilter.insertAdjacentHTML('beforeend', `<option value="${val}">${val}</option>`);
    }

    for (const {title} of Object.values(meta)) {
        headerRow.insertAdjacentHTML('beforeend', `<th scope="col">${title}</th>`)
    }

    const pages = json.pages;

    renderPages(pages, meta);

    toolFilter.addEventListener('change', () => {
        renderPages(filter(pages, meta), meta);
    });

    publishedFilter.addEventListener('change', () => {
        renderPages(filter(pages, meta), meta);
    });

    authorFilter.addEventListener('change', () => {
        renderPages(filter(pages, meta), meta);
    });

    searchInput.addEventListener('input', () => {
        renderPages(filter(pages, meta), meta);
    });
});