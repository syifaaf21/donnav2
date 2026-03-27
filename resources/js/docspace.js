import '@onlyoffice/docspace-sdk-js'

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('ds-frame')
    if (!el) return  // hanya jalan di halaman editor

    const docspaceUrl = el.dataset.src
    const backUrl     = el.dataset.back

    window.DocSpace.SDK.initEditor({
        src:     docspaceUrl,
        frameId: 'ds-frame',
        width:   '100%',
        height:  '100%',
        mode:    'editor',
        events: {
            onAppReady: function () {
                const overlay = document.getElementById('loadingOverlay')
                if (overlay) overlay.classList.add('hidden')
            },
            onEditorCloseCallback: function () {
                window.location.href = backUrl
            },
            onAppError: function (e) {
                const overlay = document.getElementById('loadingOverlay')
                if (overlay) overlay.innerHTML =
                    '<div style="color:#e05252;font-size:14px;">⚠ ' + e + '</div>'
            }
        }
    })
})
