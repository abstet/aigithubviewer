<template>
    <div id="githubviewer">
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

        <div class="row-cols-8">
            <div class="col-md-4">
                <h5>Repositories</h5>
                <ul class="list-group">
                    <li v-for="repo in repos" class="list-group-item" @click="loadRepo(repo.owner, repo.repo)" style="cursor: pointer;">
                        <span>{{ repo.owner }}/{{ repo.repo }}</span>
                        <button class="btn btn-default btn-xs pull-right refresh-item" @click="importRepo(repo.owner, repo.repo)" title="Re-import">
                            <span class="glyphicon glyphicon-refresh"></span>
                        </button>
                        <button class="btn btn-default btn-xs pull-right remove-item" @click="deleteRepo(repo.owner, repo.repo)" title="Delete">
                            <span class="glyphicon glyphicon-remove"></span>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="col-md-8">
                <div v-if="commitsTotal">
                    <button class="btn btn-sm btn-warning pull-right" @click="clearCurrentRepo()">Remove all</button>
                    <button class="btn btn-sm btn-default pull-right" @click="deleteCommits()">Remove selected</button>
                </div>

                <h5>Commits
                    <span v-if="commitsTotal > 0"> : {{ commitsTotal }}</span>
                </h5>


                <ul class="list-group">
                    <li v-for="commit in commits.data" class="list-group-item">
                        <input type="checkbox" v-bind:name="commit.sha" data-content="checkCommits"/>
                        <a v-bind:href="commit.json.commit.url" target="_blank">{{ commit.json.commit.author.date }} by <em>{{ commit.json.commit.author.name }}</em></a>
                    </li>
                </ul>

                <pagination :data="commits" @pagination-change-page="loadCommits"></pagination>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from "axios";
    axios.interceptors.request.use(function(config) {
        $('#githubviewer').addClass("overlay");
        return config;
    }, function (error) {
        $('#githubviewer').removeClass("overlay");
        return false;
    });
    axios.interceptors.response.use(function(response) {
        $('#githubviewer').removeClass("overlay");
        return response;
    }, function (error) {
        $('#githubviewer').removeClass("overlay");
        return false;
    });

    export default {
        data() {
            return {
                commits: {},
                commitsTotal: 0,
                repos: {},
                repoOwner: null,
                repoRepo: null
            }
        },
        mounted() {
            this.loadRepos();
        },
        methods: {
            deleteCommits() {
                let sha = [];
                $('[data-content="checkCommits"]').each(function( index ) {
                    if (this.checked == true) {
                        sha.push($(this).attr('name'));
                    }
                });

                if (sha.length) {
                    axios.post('/github/repo/commits', sha)
                        .then(response => {
                            this.loadCommits();
                        });
                }
            },
            loadCommits(page = 1) {
                this.commits = {};
                axios.post('/github/repo/' + this.repoOwner + '/' + this.repoRepo + '/?page=' + page)
                    .then(response => {
                        //console.log(response.data);
                        if (response.data.status != 'error') {
                            if (response.data) {
                                this.commits = response.data;
                                this.commits.data.map((item) => {
                                    item.json = JSON.parse(item.json);
                                });
                                this.commitsTotal = response.data.total;
                            }
                        }
                    });
            },
            deleteRepo(owner, repo) {
                axios.delete('/github/repo/'+owner+'/'+repo)
                    .then(response => {
                        //console.log(response.data);
                        this.repoOwner = null;
                        this.repoRepo = null;
                        this.loadRepos();
                        this.loadCommits();
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

<style>
    .overlay {
        opacity: .5;
    }
</style>

