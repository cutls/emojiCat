const app = new Vue({
    el: '#app',
    data: {
        nokori: "loading",
        user: false,
        error: null,
        question: false,
        complete: false,
        count: 0,
        data: {
            image: null,
            shortcode: null
        },
        selCat: null,
        cats: category,
        name: siteName
    },
    methods: {
        window: onload = function () {
            if (localStorage.getItem("token")) {
                console.log("loginned")
                app.user = true
            }
            onloader()
        },
        login: function (event) {
            const start = "https://" + domain + "/api/v1/apps"
            const httpreq = new XMLHttpRequest()
            httpreq.open('POST', start, true)
            httpreq.setRequestHeader('Content-Type', 'application/json')
            httpreq.responseType = 'json';
            const red = location.origin + location.pathname
            httpreq.send(JSON.stringify({
                scopes: 'read',
                client_name: appName,
                redirect_uris: red
            }));
            httpreq.onreadystatechange = function () {
                if (httpreq.readyState == 4) {
                    let json = httpreq.response
                    const auth = "https://" + domain + "/oauth/authorize?client_id=" + json[
                        "client_id"] + "&client_secret=" + json["client_secret"] +
                        "&response_type=code&scope=read&redirect_uri=" + encodeURIComponent(
                            red)
                    localStorage.setItem("client_id", json["client_id"])
                    localStorage.setItem("client_secret", json["client_secret"])
                    location.href = auth
                }
            }
        },
        getNokori: function (event) {
            let start = "./nokori"
            let httpreq = new XMLHttpRequest()
            httpreq.open('GET', start, true)
            httpreq.responseType = 'json'
            httpreq.onreadystatechange = function () {
                if (httpreq.readyState == 4) {
                    let json = httpreq.response
                    if (json.status == "success") {
                        let nokori = json.nokori
                        app.nokori = nokori
                    } else {
                        app.error = json.data_ja
                    }
                }
            }
            httpreq.send();
        },
        start: function (event) {
            this.question = true
            this.complete = false
            this.count = 0
            app.next()
        },
        next: function (event) {
            this.count++
            if (app.count > 10) {
                app.complete = true
                app.question = false
                app.getNokori()
            }
            let start = "./get"
            let httpreq = new XMLHttpRequest()
            httpreq.open('GET', start, true)
            httpreq.responseType = 'json'
            httpreq.send();
            httpreq.onreadystatechange = function () {
                if (httpreq.readyState == 4) {
                    let json = httpreq.response
                    if (json.status == "success") {
                        app.data.image = json.image
                        app.data.shortcode = json.shortcode
                    } else {
                        app.error = json.data_ja
                    }
                }
            }
        },
        post: function (event) {
            let cat = this.selCat
            let start = "./post"
            let httpreq = new XMLHttpRequest()
            httpreq.open('POST', start, true)
            httpreq.setRequestHeader('Content-Type', 'application/json')
            httpreq.responseType = 'json'
            let tk = localStorage.getItem("token")
            httpreq.send(JSON.stringify({
                token: tk,
                shortcode: app.data.shortcode,
                cat: cat
            }));
            httpreq.onreadystatechange = function () {
                if (httpreq.readyState == 4) {
                    let json = httpreq.response
                    if (json.status == "success") {
                        app.nokori = app.nokori - 1
                        app.next()
                    } else {
                        app.error = json.data_ja
                    }
                }
            }
        }
    }
})
function onloader() {
    document.title = siteName
    app.getNokori()
    let m = location.search.match(/\?code=([a-zA-Z-0-9-_+=]+)/);
    if (!m) {
        return
    }
    let start = "https://" + domain + "/oauth/token";
    let id = localStorage.getItem("client_id")
    let secret = localStorage.getItem("client_secret")
    localStorage.removeItem("client_id")
    localStorage.removeItem("client_secret")
    let httpreq = new XMLHttpRequest()
    httpreq.open('POST', start, true)
    httpreq.setRequestHeader('Content-Type', 'application/json')
    httpreq.responseType = 'json'
    httpreq.send(JSON.stringify({
        grant_type: "authorization_code",
        redirect_uri: location.origin + location.pathname,
        client_id: id,
        client_secret: secret,
        code: m[1]
    }))
    httpreq.onreadystatechange = function () {
        if (httpreq.readyState == 4) {
            let json = httpreq.response
            if (json["access_token"]) {
                gettoken(json["access_token"])
            }
        }
    }
}

function gettoken(at) {
    let start = "./login"
    let httpreq = new XMLHttpRequest()
    httpreq.open('POST', start, true)
    httpreq.setRequestHeader('Content-Type', 'application/json')
    httpreq.responseType = 'json'
    httpreq.send(JSON.stringify({
        access_token: at
    }));
    httpreq.onreadystatechange = function () {
        if (httpreq.readyState == 4) {
            let json = httpreq.response
            if (json.status == "success") {
                localStorage.setItem("token", json.token)
                history.pushState(null, null, location.pathname);
                app.user = true
            } else {
                app.error = json.data_ja
            }
        }
    }
}
