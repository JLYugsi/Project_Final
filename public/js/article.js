Ext.define('App.model.Article', {
    extend: 'Ext.data.Model', 
    fields: [
        {name: 'id', type: 'int', mapping: 'publication_id'}, 
        {name: 'titulo', type: 'string'},
        {name: 'description', type: 'string'},
        {name: 'publicationDate', type: 'date', dateFormat: 'Y-m-d'}, 
        {name: 'author_id', mapping: 'author_id', type: 'int'}, 


        {
            name: 'authorName',
            convert: (v, rec) => {
                const a = rec.get('author'); 
                return a ? `${a.firstname} ${a.lastname}` : '';
            }
        },

        {name: 'DOI', type: 'string'}, 
        {name: 'abstract', type: 'string'},
        {name: 'keywords', type: 'string'},
        {name: 'indexation', type: 'string'},
        {name: 'magazine', type: 'string'},
        {name: 'area', mapping: 'area', type: 'string'} 
    ]
});


Ext.create('Ext.data.Store', {
    storeId: 'articleStore', 
    model: 'App.model.Article', 
    proxy: {
        type: 'rest', 
        url: 'api/article.php', 
        reader: {
            type: 'json', 
            rootProperty: '' 
        },
        writer: {
            type: 'json', 
            rootProperty: '', 
            writeAllFields: true 
        },
        appendId: false 
    },
    autoLoad: true, 
    autoSync: false 
});


const createArticlesPanel = () => {
    return Ext.create('Ext.grid.Panel', {
        title: 'Artículos', 
        store: Ext.getStore('articleStore'), 
        itemId: 'articleGrid', 
        layout: 'fit', 
        columns: [ 
            { text: 'ID', width: 40, dataIndex: 'id' }, 
            { text: 'Título', flex: 1, dataIndex: 'titulo' }, 
            { text: 'Descripción', flex: 1, dataIndex: 'description' }, 
            {
                text: 'Fecha Publicación', 
                flex: 1,
                dataIndex: 'publicationDate',
                xtype: 'datecolumn', 
                format: 'Y-m-d' 
            },
            { text: 'Author', flex: 1, dataIndex: 'authorName' }, 
            { text: 'DOI', flex: 1, dataIndex: 'DOI' }, 
            { text: 'Abstract', flex: 1, dataIndex: 'abstract' }, 
            { text: 'Palabras Clave', flex: 1, dataIndex: 'keywords' }, 
            { text: 'Indexación', flex: 1, dataIndex: 'indexation' }, 
            { text: 'Revista', flex: 1, dataIndex: 'magazine' }, 
            { text: 'Área Conocimiento', flex: 1, dataIndex: 'area' }
        ],
        tbar: [
            {text: 'Add'},
            {text: 'Update'},
            {text: 'Delete'}
        ]
    });
};

window.createArticlesPanel = createArticlesPanel;