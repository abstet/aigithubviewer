<template>
    <div>
        <form>
            <div class="form-group">
                <div class="input-group">
                    <input type="text" class="form-control" id="repoName" placeholder="owner/repo" />
                    <div class="input-group-btn">
                        <button @click="importRepoForm()" class="form-control btn-success ">
                            <i class="glyphicon glyphicon-import"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="row-cols-8">
            <div class="col-md-4">
                <h5>Repositories</h5>
                <ul class="list-group">
                    <a href="#" v-for="repo in repos" class="list-group-item" @click="loadRepo(repo.owner, repo.repo)">
                        <span>{{ repo.owner }}/{{ repo.repo }}</span>
                        <button class="btn btn-default btn-xs pull-right refresh-item" @click="importRepo(repo.owner, repo.repo)" title="Re-import">
                            <span class="glyphicon glyphicon-refresh"></span>
                        </button>
                        <button class="btn btn-default btn-xs pull-right remove-item" @click="deleteRepo(repo.owner, repo.repo)" title="Delete">
                            <span class="glyphicon glyphicon-remove"></span>
                        </button>
                    </a>
                </ul>
            </div>
            <div class="col-md-6">
                <h5>Commits</h5>
                <ul class="list-group">
                    <li v-for="commit in commits.data" class="list-group-item">
                        {{ commit.json.commit.author.date }} by <em>{{ commit.json.commit.author.name }}</em>
                    </li>
                </ul>

                <pagination :data="commits" @pagination-change-page="loadCommits"></pagination>
                <div v-if="commits.data">
                    <button class="btn btn-default btn pull-right" @click="clearCurrentRepo()">Mass deletion</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from "axios";

    export default {
        data() {
            return {
                commits: {},
                repos: {},
                repoOwner: null,
                repoRepo: null
            }
        },
        mounted() {
            this.loadRepos();
        },
        methods: {
            loadCommits(page = 1) {
                axios.post('/github/repo/' + this.repoOwner + '/' + this.repoRepo + '/?page=' + page)
                    .then(response => {
                        //console.log(response.data);
                        if (response.data) {
                            this.commits = response.data;
                            this.commits.data.map((item) => {
                                item.json = JSON.parse(item.json);
                            });
                        }
                    });
            },
            deleteRepo(owner, repo) {
                axios.delete('/github/repo/'+owner+'/'+repo)
                    .then(response => {
                        console.log(response.data);
                        this.repoOwner = null;
                        this.repoRepo = null;
                        this.loadRepos();
                    });
            },
            clearCurrentRepo() {
                axios.patch('/github/repo/' + this.repoOwner + '/' + this.repoOwner)
                    .then(response => {
                        this.loadRepo(this.repoOwner, this.repoOwner);
                    });
            },
            loadRepo(owner, repo) {
                this.repoOwner = owner;
                this.repoRepo = repo;
                this.loadCommits();
            },
            loadRepos() {
                axios.post('/github/repo')
                    .then(response => {
                        this.repos = response.data.data.map((item) => {
                            return item;
                        });
                    });
            },
            importRepoForm() {
                let name = $('#repoName').val();
                if (name.length) {
                    let a = name.split('/');
                    this.importRepo(a[0], a[1]);
                }
            },
            importRepo(name, repo) {
                axios.put('/github/repo/' + name + '/' + repo)
                    .then(response => {
                        // console.log(response.data);
                        this.loadRepos();
                        this.loadRepo(name, repo);
                    });
            }
        }
    }
</script>
