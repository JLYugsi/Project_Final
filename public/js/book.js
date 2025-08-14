Ext.define('App.model.Book', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'id', type: 'int' },
        { name: 'titulo', type: 'string' },
        { name: 'description', type: 'string' },
        { name: 'publication_date', type: 'date', dateFormat: 'Y-m-d' },
        { name: 'author_id', mapping: 'author.id', type: 'int' },
        {
            name: 'authorName',
            convert: (v, rec) => {
                const a = rec.get('author');
                // Asegúrate de que los campos del autor sean 'first_Name' y 'last_Name'
                // según la definición de tu modelo App.model.Author
                return a ? `${a.first_name} ${a.last_name}` : '';
            }
        },
        { name: 'isbn', type: 'string' },
        { name: 'genre', type: 'string' },
        { name: 'edition', type: 'int' }
    ],
});

Ext.create('Ext.data.Store', {
    storeId: 'bookStore',
    model: 'App.model.Book',
    proxy: {
        type: 'rest',
        url: 'api/book.php',
        reader: { type: 'json', rootProperty: '' },
        writer: { type: 'json', rootProperty: '', writeAllFields: true },
        appendId: false
    },
    autoLoad: true,
    autoSync: false
});

const createBooksPanel = () => {
    const openDialog = (rec, isNew) => {
        const win = Ext.create('Ext.window.Window', {
            title: isNew ? 'Add Book' : 'Edit Book',
            modal: true,
            width: 640,
            height: 380, // Aumentada la altura para que quepan los botones
            layout: 'fit',
            items: [{
                xtype: 'form',
                bodyPadding: 12,
                defaults: { anchor: '100%' },
                items: [
                    { xtype: 'textfield', name: 'id', fieldLabel: 'ID', hidden: true }, // Ocultar ID
                    { xtype: 'textfield', name: 'titulo', fieldLabel: 'Title', allowBlank: false },
                    { xtype: 'textfield', name: 'description', fieldLabel: 'Description', allowBlank: false },
                    {
                        xtype: 'datefield',
                        name: 'publication_date',
                        fieldLabel: 'Publication Date',
                        format: 'Y-m-d',
                        submitFormat: 'Y-m-d',
                        allowBlank: false
                    },
                    { xtype: 'textfield', name: 'isbn', fieldLabel: 'ISBN', allowBlank: false },
                    { xtype: 'textfield', name: 'genre', fieldLabel: 'Genre', allowBlank: false },
                    { xtype: 'textfield', name: 'edition', fieldLabel: 'Edition', allowBlank: false },
                    authorcomboboxCfg
                ],
                buttons: [ // Los botones van aquí, dentro del formulario o en el fbar de la ventana
                    {
                        text: 'Save',
                        handler: function() {
                            const form = this.up('form').getForm();
                            const bookStore = Ext.getStore('bookStore');

                            if (form.isValid()) {
                                form.updateRecord(rec);

                                if (isNew) {
                                    bookStore.add(rec);
                                }

                                bookStore.sync({ // Sincroniza los cambios con el backend
                                    success: () => {
                                        Ext.Msg.alert('Success', 'Se ha ingresado correctamente.');
                                        win.close();
                                    },
                                    failure: (batch, options) => {
                                        let errorMessage = 'Error al guardar el libro.';
                                        if (batch.exceptions && batch.exceptions[0] && batch.exceptions[0].response) {
                                            errorMessage += '\nRespuesta del servidor: ' + batch.exceptions[0].response.responseText;
                                        }
                                        Ext.Msg.alert('Error', errorMessage);
                                        bookStore.rejectChanges(); // Revertir si falla
                                    }
                                });
                            } else {
                                Ext.Msg.alert('Error', 'Por favor complete todos los campos.');
                            }
                        }
                    },
                    {
                        text: 'Cancel',
                        handler: function() {
                            win.close();
                        }
                    }
                ]
            }]
        });
        win.down('form').loadRecord(rec);
        win.show();
    };

    const authorcomboboxCfg = {
        xtype: 'combobox',
        name: 'author', // El 'name' debe coincidir con el campo en el modelo Book para guardar.
        fieldLabel: 'Author',
        store: Ext.getStore('AuthorStore'), // Asumiendo que AuthorStore está definido y cargado
        queryMode: 'local',
        valueField: 'id',
        displayField: 'fullName', // Asumiendo que Author tiene un campo 'fullName'
        forceSelection: true,
        displayTpl: Ext.create('Ext.XTemplate', '{fullName}'),
        allowBlank: false,
    };

    return Ext.create('Ext.grid.Panel', {
        title: 'Books',
        store: Ext.getStore('bookStore'),
        itemId: 'bookGrid',
        layout: 'fit',
        columns: [
            { text: 'ID', width: 40, dataIndex: 'id' },
            { text: 'Title', flex: 1, dataIndex: 'titulo' },
            { text: 'Description', flex: 1, dataIndex: 'description' },
            { text: 'Publication Date', flex: 1, dataIndex: 'publication_date', xtype: 'datecolumn', format: 'Y-m-d' },
            { text: 'Author', flex: 1, dataIndex: 'authorName' },
            { text: 'ISBN', flex: 1, dataIndex: 'isbn' },
            { text: 'Genre', flex: 1, dataIndex: 'genre' },
            { text: 'Edition', flex: 1, dataIndex: 'edition' }
        ],
        tbar: [
            {
                text: 'Add',
                handler: () => openDialog(Ext.create('App.model.Book'), true)
            },
            {
                text: 'Update',
                handler: function() {
                    const grid = this.up('grid');
                    const selectedRecord = grid.getSelectionModel().getSelection()[0];
                    if (selectedRecord) {
                        openDialog(selectedRecord, false);
                    } else {
                        Ext.Msg.alert('Selection', 'Por favor, selecciona un libro para actualizar.');
                    }
                }
            },
            // Aquí va el botón Delete
            {
                text: 'Delete',
                handler() {
                    const grid = this.up('grid');
                    const rec = grid.getSelectionModel().getSelection()[0]; // getSelectionModel().getSelection()
                    if (!rec) {
                        return Ext.Msg.alert('Warning', 'Selecciona un libro para eliminar.');
                    }

                    Ext.Msg.confirm('Confirmar', '¿Eliminar este libro?', btn => {
                        if (btn === 'yes') {
                            const bookStore = Ext.getStore('bookStore'); // Obtener el store
                            bookStore.remove(rec);
                            bookStore.sync({
                                success: () => Ext.Msg.alert('Éxito', 'Eliminado correctamente'),
                                failure: (batch, options) => {
                                    let errorMessage = 'Fallo al eliminar.';
                                    if (batch.exceptions && batch.exceptions[0] && batch.exceptions[0].response) {
                                        errorMessage += '\nRespuesta del servidor: ' + batch.exceptions[0].response.responseText;
                                    }
                                    Ext.Msg.alert('Error', errorMessage);
                                    bookStore.rejectChanges(); // Revertir los cambios si falla
                                }
                            });
                        }
                    });
                }
            }
        ]
    });
};

window.createBookPanel = createBooksPanel; // Asegúrate de que sea createBookPanel si así lo llamas en app.js