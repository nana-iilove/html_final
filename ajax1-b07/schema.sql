-- schema.sql
-- Assignment-8: 投票系統資料表設計

CREATE TABLE IF NOT EXISTS activities (
    id          INTEGER      PRIMARY KEY AUTOINCREMENT,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    creator     VARCHAR(50)  NOT NULL,
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS candidates (
    id          INTEGER      PRIMARY KEY AUTOINCREMENT,
    activity_id INTEGER      NOT NULL,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    image       VARCHAR(255),
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS votes (
    id          INTEGER     PRIMARY KEY AUTOINCREMENT,
    activity_id INTEGER     NOT NULL,
    candidate_id INTEGER    NOT NULL,
    voter_name  VARCHAR(50) NOT NULL,
    voted_at    DATETIME    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id)  REFERENCES activities(id)  ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id)  ON DELETE CASCADE,
    -- 同一使用者名稱在同一活動中只能投一票
    UNIQUE (activity_id, voter_name)
);
