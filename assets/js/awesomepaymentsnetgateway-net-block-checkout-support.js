( function( blocks, i18n, element, components, editor ) {
    const { registerPaymentMethod } = wc.wcBlocksRegistry;
    // Use the localized data from PHP
    const awesomepaymentsnetgateways = awesomepaymentsnetData || [];
    awesomepaymentsnetgateways.forEach( ( awesomepaymentsnet ) => {
        registerPaymentMethod({
            name: awesomepaymentsnet.id,
            label: awesomepaymentsnet.label,
            ariaLabel: awesomepaymentsnet.label,
            content: element.createElement(
                'div',
                { className: 'awesomepaymentsnet-method-wrapper' },
                element.createElement(
                    'div',
                    { className: 'awesomepaymentsnet-method-label' },
                    '' + awesomepaymentsnet.description
                ),
                awesomepaymentsnet.icon_url ? element.createElement(
                    'img',
                    {
                        src: awesomepaymentsnet.icon_url,
                        alt: awesomepaymentsnet.label,
                        className: 'awesomepaymentsnet-method-icon'
                    }
                ) : null
            ),
            edit: element.createElement(
                'div',
                { className: 'awesomepaymentsnet-method-wrapper' },
                element.createElement(
                    'div',
                    { className: 'awesomepaymentsnet-method-label' },
                    '' + awesomepaymentsnet.description
                ),
                awesomepaymentsnet.icon_url ? element.createElement(
                    'img',
                    {
                        src: awesomepaymentsnet.icon_url,
                        alt: awesomepaymentsnet.label,
                        className: 'awesomepaymentsnet-method-icon'
                    }
                ) : null
            ),
            canMakePayment: () => true,
        });
    });
} )(
    window.wp.blocks,
    window.wp.i18n,
    window.wp.element,
    window.wp.components,
    window.wp.blockEditor
);
