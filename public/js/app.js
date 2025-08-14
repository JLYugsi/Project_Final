Ext.onReady(() => {
  // Crear cada panel invocando la función de su archivo
  const authorsPanel  = createAuthorsPanel();
  const articlesPanel = createArticlesPanel();
  const booksPanel    = createBooksPanel();

  // Panel principal con Card Layout
  const mainCard = Ext.create('Ext.panel.Panel', {
    region : 'center',
    layout : 'card',
    items  : [ authorsPanel, articlesPanel, booksPanel ]
  });

  // Viewport con navbar arriba
  Ext.create('Ext.container.Viewport', {
    id     : 'mainViewport',
    layout : 'border',
    items  : [
      {
        region : 'north',
        xtype  : 'toolbar',
        items  : [
          {
            text   : 'Authors',
            handler: () => mainCard.getLayout().setActiveItem(authorsPanel)
          },
          {
            text   : 'Articles',
            handler: () => mainCard.getLayout().setActiveItem(articlesPanel)
          },
          {
            text   : 'Books',
            handler: () => mainCard.getLayout().setActiveItem(booksPanel)
          }
        ]
      },
      mainCard
    ]
  });
});