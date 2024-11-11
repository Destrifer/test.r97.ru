const request = {
    post: async function(url, filter) {
        try {
            let data;
            if (!filter.append) {
                data = new FormData();
                for (let i in filter) {
                    data.append(i, filter[i]);
                }
            } else {
                data = filter;
            }
            const response = await fetch(url, {
                method: 'POST',
                mode: 'same-origin',
                credentials: 'same-origin',
                body: data
            });
            if (!response.ok) {
                throw response;
            }
            return await response.json();
        } catch (err) {
            console.error(err);
            return {};
        }
    }
}