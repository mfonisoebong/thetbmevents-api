# Event Recommendation Engine Guide (for `thetbmevents-api`)

## 1) Goal and Scope

Build a recommendation system that supports:

- **Basic feeds**: `new`, `hot`, `trending`
- **Advanced feeds**: personalized recommendations per user
- **Product constraints**: scalable, explainable, robust to spam/gaming, easy to iterate

This guide is intentionally math-heavy and implementation-ready for a Laravel API.

---

## 2) Product Framing as an Optimization Problem

Let an impression be `(u, e, t)` where:

- `u` = user
- `e` = event
- `t` = serving time

For each candidate event, estimate utility:

$$
\text{Utility}(u,e,t) = \alpha\,P(\text{click}|u,e,t) + \beta\,P(\text{purchase}|u,e,t) + \gamma\,\text{RetainScore}(u,e)
$$

Then rank events by utility (plus constraints like diversity and freshness).

### Practical objective choices

- Early stage: maximize **CTR** while keeping good freshness/diversity
- Mid stage: maximize **ticket conversion**
- Mature stage: maximize **expected revenue and retention**

---

## 3) Data You Must Collect (Foundation)

Without logs, no recommendation engine survives.

### 3.1 User events to track

- `impression` (event shown)
- `click` (event opened)
- `favorite/like`
- `add_to_cart`
- `purchase`
- `share`
- `dwell_time` (seconds)
- `dismiss/not_interested`

### 3.2 Event metadata to track

- category/subcategory tags
- location (city, lat/lng)
- start time/date
- price band
- organizer quality priors (historical performance)
- event age

### 3.3 Suggested DB tables

- `user_event_interactions` (fact table)
- `event_popularity_snapshots` (hourly/daily aggregates)
- `user_taste_profiles` (vectorized preferences)
- `recommendation_impressions` (for offline replay + experiments)

### 3.4 Why this matters statistically

You need enough signal for:

- probability estimates (CTR/CVR)
- confidence intervals
- shrinkage (Bayesian priors) to avoid noisy winners

---

## 4) Core Buckets: New, Hot, Trending

These can be shipped fast and provide immediate improvement over pure reverse-chronological listing.

## 4.1 New

Simple and reliable freshness score:

$$
S_{new}(e,t)=\exp\left(-\frac{t-t_{created}(e)}{\tau_{new}}\right)
$$

- `tau_new` controls freshness half-life (e.g., 48h)
- filter to `status = published`

Use as:

- dedicated `new` feed
- boost term in global ranking

## 4.2 Hot

Hot means strong engagement and conversion now (not only volume).

Define weighted interactions over a sliding window `W` (e.g., last 24h):

$$
I_e = w_1\,\text{clicks}_e + w_2\,\text{likes}_e + w_3\,\text{cart}_e + w_4\,\text{purchases}_e
$$

Recommended base weights (tune later):

- `w1=1`, `w2=2`, `w3=4`, `w4=8`

Add Bayesian shrinkage for robustness:

$$
S_{hot}(e) = \frac{I_e + m\mu}{n_e + m}
$$

- `n_e` = interaction opportunities (or impressions)
- `mu` = global average interaction quality
- `m` = prior strength (e.g., 50)

This avoids tiny-sample spikes dominating ranking.

## 4.3 Trending

Trending is acceleration, not just popularity level.

Let `R_t(e)` be normalized engagement rate in current window and `R_{t-1}(e)` previous window.

$$
S_{trend}(e)=\frac{R_t(e)-R_{t-1}(e)}{\sigma_e + \epsilon}
$$

Equivalent robust option using z-score of first differences:

$$
\Delta R_t(e)=R_t(e)-R_{t-1}(e),\quad
S_{trend}(e)=\frac{\Delta R_t(e)-\mu_{\Delta}}{\sigma_{\Delta}+\epsilon}
$$

Use minimum support thresholds to prevent noise:

- impressions >= `N_min`
- interactions >= `K_min`

---

## 5) Personalization: Hybrid Ranking

A practical architecture is **hybrid**:

- candidate generation from multiple sources
- learned or heuristic re-ranking for each user

## 5.1 Candidate generation sources

For user `u`, generate candidates from:

- fresh events (`new`)
- popular events (`hot`)
- rising events (`trending`)
- content similarity (category, tags, location, price)
- collaborative filtering neighbors
- organizer affinity

Union top-k from each source, then deduplicate.

## 5.2 User taste profile (content vector)

Represent event `e` by feature vector `x_e` (one-hot tags, embeddings, normalized price, geo features).

Build user profile as time-decayed average:

$$
p_u = \frac{\sum_{i \in \mathcal{H}_u}\omega_i x_i}{\sum_{i \in \mathcal{H}_u}\omega_i},
\quad
\omega_i = a_{action(i)}\exp\left(-\lambda\Delta t_i\right)
$$

- `a_action`: purchase > cart > click
- recency decay via `lambda`

Similarity score:

$$
S_{content}(u,e)=\cos(p_u, x_e)
$$

## 5.3 Collaborative filtering signal

Use implicit feedback matrix `R` (users x events), weighted by action strengths.

Options:

- item-item cosine similarity
- matrix factorization (ALS/BPR)

Simple item-based score:

$$
S_{cf}(u,e)=\sum_{j\in\mathcal{H}_u} \text{sim}(e,j)\cdot r_{u,j}
$$

## 5.4 Unified personalized score

$$
S(u,e,t)=\theta_1 S_{new}(e,t)+\theta_2 S_{hot}(e,t)+\theta_3 S_{trend}(e,t)+\theta_4 S_{content}(u,e)+\theta_5 S_{cf}(u,e)+\theta_6 S_{geo}(u,e)+\theta_7 S_{price}(u,e)
$$

Then apply monotonic transforms and calibration (Platt/Isotonic) if needed.

---

## 6) Cold Start Strategies

## 6.1 New user cold start

- ask 3-5 interests at onboarding (categories/cities/price range)
- start with `new + hot + trending` blended by chosen interests
- use contextual bandits for fast adaptation

## 6.2 New event cold start

- use content-based score from metadata
- inject exploration traffic quota
- apply organizer prior quality score

Bayesian prior for conversion probability:

$$
\hat{p}_e = \frac{c_e + \alpha}{n_e + \alpha + \beta}
$$

---

## 7) Exploration vs Exploitation (Bandits)

Use epsilon-greedy or Thompson Sampling at ranking layer.

## 7.1 Epsilon-greedy

- with probability `epsilon`: sample from under-exposed candidates
- else: show top-ranked results

Decay epsilon over time by user maturity.

## 7.2 Thompson Sampling (Bernoulli rewards)

Per event maintain Beta posterior:

$$
p_e \sim \text{Beta}(\alpha_e, \beta_e)
$$

Sample `p_e` and rank by sampled score (or blended with relevance).

---

## 8) Diversity, Fairness, and Anti-Gaming

## 8.1 Diversity constraints

After scoring, apply reranker with constraints:

- category diversity
- organizer diversity
- location diversity
- avoid near-duplicate events

Example objective:

$$
\max \sum_{e\in L} S(u,e) + \lambda\,\text{Diversity}(L)
$$

## 8.2 Fairness controls

- prevent monopoly by top organizers
- cap repeated exposure for same organizer/event
- controlled boost for long-tail events

## 8.3 Abuse resistance

- bot/spam detection on interactions
- robust stats (winsorization, median-of-means)
- anomaly flags for sudden suspicious spikes

---

## 9) Offline and Online Evaluation

## 9.1 Offline metrics

- Precision@K
- Recall@K
- MAP@K
- NDCG@K
- Coverage
- Novelty

Also track calibration and segment-level performance (new vs returning users).

## 9.2 Online metrics (A/B)

Primary:

- CTR
- add-to-cart rate
- purchase conversion rate
- revenue/session

Guardrails:

- latency
- bounce rate
- complaint/not-interested rate

### Experiment design notes

- power analysis before launch
- sequential testing correction
- CUPED for variance reduction (optional advanced)

---

## 10) Recommended System Architecture (Laravel-Friendly)

## 10.1 Services layer

Create service classes:

- `App/Services/Recommendations/CandidateGenerator.php`
- `App/Services/Recommendations/FeatureBuilder.php`
- `App/Services/Recommendations/ScoreCalculator.php`
- `App/Services/Recommendations/ReRanker.php`
- `App/Services/Recommendations/RecommendationService.php`

## 10.2 Batch + online split

- batch jobs compute aggregates (`hot`, `trending`, profiles)
- online endpoint fetches candidates and computes final ranking
- cache hot artifacts in Redis

## 10.3 Suggested endpoint evolution

In `app/Http/Controllers/V2/EventController.php`:

- keep existing endpoints for backward compatibility
- add:
  - `GET /v2/events/recommendations?type=new|hot|trending|personalized`
  - `GET /v2/events/recommendations/personalized`

---

## 11) API Contract Example

Request:

```http
GET /api/v2/events/recommendations?type=personalized&limit=20
Authorization: Bearer <token>
```

Response (example):

```json
{
  "message": "Recommendations fetched successfully",
  "data": [
    {
      "id": "evt_123",
      "title": "Lagos AI Meetup",
      "score": 0.873,
      "reason": ["matches_interest:technology", "trending_in_city", "similar_to_liked_events"]
    }
  ],
  "meta": {
    "model_version": "hybrid_v1",
    "experiment_bucket": "B"
  }
}
```

---

## 12) Ranking Pipeline Pseudocode

```text
Input: user u, request context c
1. candidates = union(
      top_new(),
      top_hot(),
      top_trending(),
      content_neighbors(u),
      cf_neighbors(u)
   )
2. features = build_features(u, candidates, c)
3. base_scores = weighted_hybrid(features)
4. calibrated = calibrate(base_scores)
5. reranked = apply_diversity_and_business_rules(calibrated)
6. explored = exploration_policy(reranked, u)
7. log_impressions(u, explored)
8. return top_k(explored)
```

---

## 13) Rollout Plan (Practical)

## Phase 1 (1-2 weeks)

- implement `new`, `hot`, `trending` scores
- add interaction logging + aggregates
- add recommendation endpoint with `type` parameter

## Phase 2 (2-4 weeks)

- implement user profiles + content similarity
- personalized reranking on top of phase 1 candidates
- basic diversity constraints

## Phase 3 (4-8 weeks)

- collaborative filtering (item-item first)
- bandit exploration policy
- A/B experimentation framework

## Phase 4 (ongoing)

- move from weighted heuristics to learned ranker (GBDT/NN)
- model monitoring, drift detection, periodic retraining

---

## 14) Practical Starting Weights (Good Default)

For a first personalized ranker:

- `theta_new = 0.15`
- `theta_hot = 0.20`
- `theta_trend = 0.15`
- `theta_content = 0.30`
- `theta_cf = 0.10`
- `theta_geo = 0.05`
- `theta_price = 0.05`

Tune using Bayesian optimization or grid search over offline validation.

---

## 15) What to Build First in Your Existing Controller

Current `listRecentEvents()` is chronological only. Immediate upgrade path:

1. keep chronological endpoint unchanged for compatibility
2. add `new`, `hot`, `trending` service-backed methods
3. add `personalized` endpoint that requires authenticated user
4. return `reason` metadata for explainability
5. add feature flags so rollout can be gradual

---

## 16) Common Failure Modes and Fixes

- popularity collapse -> increase diversity penalties and exploration
- noisy spikes -> Bayesian shrinkage + min support thresholds
- stale recommendations -> stronger time decay + faster refresh jobs
- filter bubbles -> enforce category/organizer mix constraints
- cold start frustration -> onboarding preferences + broad exploration

---

## 17) Minimal SQL/Pipeline Ideas

- hourly materialized aggregates per event:
  - impressions, clicks, likes, carts, purchases
- precompute:
  - `hot_score`, `trend_score`, `new_score`
- user profile refresh job every N hours
- cache top feeds by city/category in Redis sorted sets

---

## 18) Final Notes

- Build logs first, then ranking complexity.
- Prefer robust statistics over naive counts.
- Start interpretable (weighted hybrid), then learn weights from data.
- Keep system explainable to debug and improve faster.

If you want, next step is implementing this as concrete Laravel classes + migrations + endpoints directly in your project.
